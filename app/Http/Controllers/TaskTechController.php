<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TaskTechController extends Controller
{
    private $notionSecret;

    private $notionDatabaseId;

    private $notionApiUrl = 'https://api.notion.com/v1';

    public function __construct()
    {
        $this->notionSecret = config('services.notion.secret');
        $this->notionDatabaseId = '24eb52f3795c80fca6c7e048f802bce8';
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email',
                'type' => 'required|in:bug,feature',
                'url' => 'required|url',
                'assignee' => 'nullable|string|in:fcoterroba,jose,raul',
                'priority' => 'required|in:baja,media,alta',
                'message' => 'required|string|min:10|max:2000',
            ]);

            $user = Auth::user();
            $additionalData = [
                'user_id' => $user->id ?? null,
                'user_name' => $user->name ?? 'Usuario no identificado',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()->toISOString(),
                'status' => 'pending',
                'task_id' => $this->generateTaskId(),
            ];

            $taskData = array_merge($validatedData, $additionalData);

            $notionResponse = $this->createNotionPage($taskData);

            if (! $notionResponse['success']) {
                throw new \Exception($notionResponse['error']);
            }

            Log::info('Nueva tarea técnica creada en Notion', [
                'task_id' => $taskData['task_id'],
                'notion_page_id' => $notionResponse['page_id'],
                'type' => $taskData['type'],
                'priority' => $taskData['priority'],
                'assignee' => $taskData['assignee'] ?? 'Sin asignar',
                'user' => $taskData['user_name'],
            ]);

            $this->sendDiscordNotification($taskData, $notionResponse);

            return response()->json([
                'success' => true,
                'message' => $this->getSuccessMessage($taskData['type']),
                'task_id' => $taskData['task_id'],
                'notion_page_id' => $notionResponse['page_id'],
                'data' => [
                    'type' => $taskData['type'],
                    'priority' => $taskData['priority'],
                    'assignee' => $taskData['assignee'] ?? 'Sin asignar',
                    'url' => $taskData['url'],
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación en los datos enviados',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error al crear tarea técnica en Notion', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la tarea en Notion. Por favor, inténtalo de nuevo.',
            ], 500);
        }
    }

    private function createNotionPage(array $taskData): array
    {
        try {
            $priorityColors = [
                'baja' => 'green',
                'media' => 'yellow',
                'alta' => 'red',
            ];

            $typeEmojis = [
                'bug' => '🐛',
                'feature' => '✨',
            ];

            $emoji = $typeEmojis[$taskData['type']] ?? '📝';
            $priorityColors[$taskData['priority']] ?? 'gray';

            $properties = [
                'Tarea / Proyecto Principal' => [
                    'title' => [
                        [
                            'text' => [
                                'content' => $emoji.' '.ucfirst($taskData['type']).' - '.$taskData['task_id'],
                            ],
                        ],
                    ],
                ],
                'Tipo' => [
                    'select' => [
                        'name' => 'Tarea',
                    ],
                ],
                'Prioridad' => [
                    'select' => [
                        'name' => ucfirst($taskData['priority']),
                    ],
                ],
                'Estado' => [
                    'status' => [
                        'name' => 'Sin empezar',
                    ],
                ],
                'Área' => [
                    'select' => [
                        'name' => 'Tech',
                    ],
                ],
                'Descripción' => [
                    'rich_text' => [
                        [
                            'text' => [
                                'content' => $taskData['message']."\n\n---\n\n📍 **URL:** ".$taskData['url']."\n\n👤 **Reportado por:** ".$taskData['email'],
                            ],
                        ],
                    ],
                ],
                'Subtareas' => [
                    'rich_text' => [],
                ],
            ];

            $assigneeIds = [
                'jose' => '209d872b-594c-81ce-9c17-0002c9ad3cfd',
                'raul' => '209d872b-594c-81f5-b6dd-000266279791',
                'fcoterroba' => '208d872b-594c-815b-a56e-00026b86c1a3',
            ];

            if (! empty($taskData['assignee']) && isset($assigneeIds[$taskData['assignee']])) {
                $properties['Responsable'] = [
                    'people' => [
                        [
                            'object' => 'user',
                            'id' => $assigneeIds[$taskData['assignee']],
                        ],
                    ],
                ];
            }

            $children = [
                [
                    'object' => 'block',
                    'type' => 'heading_2',
                    'heading_2' => [
                        'rich_text' => [
                            [
                                'type' => 'text',
                                'text' => [
                                    'content' => 'Información Técnica',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'object' => 'block',
                    'type' => 'bulleted_list_item',
                    'bulleted_list_item' => [
                        'rich_text' => [
                            [
                                'type' => 'text',
                                'text' => [
                                    'content' => 'IP: '.$taskData['ip_address'],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'object' => 'block',
                    'type' => 'bulleted_list_item',
                    'bulleted_list_item' => [
                        'rich_text' => [
                            [
                                'type' => 'text',
                                'text' => [
                                    'content' => 'User Agent: '.substr($taskData['user_agent'], 0, 100).'...',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'object' => 'block',
                    'type' => 'bulleted_list_item',
                    'bulleted_list_item' => [
                        'rich_text' => [
                            [
                                'type' => 'text',
                                'text' => [
                                    'content' => 'Creado: '.now()->format('d/m/Y H:i:s'),
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->notionSecret,
                'Content-Type' => 'application/json',
                'Notion-Version' => '2022-06-28',
            ])->post($this->notionApiUrl.'/pages', [
                'parent' => [
                    'database_id' => $this->notionDatabaseId,
                ],
                'properties' => $properties,
                'children' => $children,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                return [
                    'success' => true,
                    'page_id' => $responseData['id'],
                    'url' => $responseData['url'] ?? null,
                ];
            } else {
                Log::error('Error en API de Notion', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Error al crear página en Notion: '.$response->status(),
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error al crear página en Notion', [
                'error' => $e->getMessage(),
                'task_data' => $taskData,
            ]);

            return [
                'success' => false,
                'error' => 'Error interno: '.$e->getMessage(),
            ];
        }
    }

    private function generateTaskId(): string
    {
        $prefix = strtoupper(substr(config('app.name'), 0, 3));
        $timestamp = now()->format('YmdHis');
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$timestamp}-{$random}";
    }

    private function getSuccessMessage(string $type): string
    {
        $messages = [
            'bug' => '¡Bug reportado correctamente! Se ha creado una página en Notion y el equipo técnico lo revisará pronto.',
            'feature' => '¡Feature solicitado correctamente! Se ha creado una página en Notion y el equipo evaluará la propuesta.',
        ];

        return $messages[$type] ?? 'Tarea creada correctamente en Notion.';
    }

    private function sendDiscordNotification(array $taskData, array $notionResponse): void
    {
        try {
            $webhookUrl = config('services.discord.webhook_url');

            if (! $webhookUrl) {
                Log::warning('Discord webhook URL no configurada');

                return;
            }

            $priorityColors = [
                'baja' => 0x00FF00,
                'media' => 0xFFFF00,
                'alta' => 0xFF0000,
            ];

            $typeEmojis = [
                'bug' => '🐛',
                'feature' => '✨',
            ];

            $emoji = $typeEmojis[$taskData['type']] ?? '📝';
            $color = $priorityColors[$taskData['priority']] ?? 0x808080;

            $discordUserIds = [
                'jose' => '658130150210273290',
                'raul' => '732554976466960496',
                'fcoterroba' => '468167743980437504',
            ];

            $content = '';
            if (! empty($taskData['assignee']) && isset($discordUserIds[$taskData['assignee']])) {
                $discordUserId = $discordUserIds[$taskData['assignee']];
                $content = "¡Nueva tarea asignada <@{$discordUserId}>!";
            } else {
                $techRoleId = '1414930506834772080';
                $content = "¡Nueva tarea sin asignar <@&{$techRoleId}>!";
            }

            $embed = [
                'title' => "{$emoji} Nueva {$taskData['type']} reportada",
                'description' => "**{$taskData['task_id']}**\n\n{$taskData['message']}",
                'color' => $color,
                'fields' => [
                    [
                        'name' => '📍 URL',
                        'value' => $taskData['url'],
                        'inline' => false,
                    ],
                    [
                        'name' => '👤 Reportado por',
                        'value' => $taskData['email'],
                        'inline' => true,
                    ],
                    [
                        'name' => '⚡ Prioridad',
                        'value' => ucfirst($taskData['priority']),
                        'inline' => true,
                    ],
                    [
                        'name' => '🏷️ Área',
                        'value' => 'Tech',
                        'inline' => true,
                    ],
                ],
                'footer' => [
                    'text' => 'Tu Trámite Fácil',
                ],
                'timestamp' => now()->toISOString(),
            ];

            if ($taskData['assignee']) {
                $embed['fields'][] = [
                    'name' => '👤 Asignado a',
                    'value' => $taskData['assignee'],
                    'inline' => true,
                ];
            }

            if (isset($notionResponse['url'])) {
                $embed['fields'][] = [
                    'name' => '🔗 Ver en Notion',
                    'value' => "[Abrir tarea]({$notionResponse['url']})",
                    'inline' => false,
                ];
            }

            $payload = [
                'content' => $content,
                'embeds' => [$embed],
            ];

            $response = Http::post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Notificación de Discord enviada correctamente', [
                    'task_id' => $taskData['task_id'],
                    'type' => $taskData['type'],
                ]);
            } else {
                Log::error('Error al enviar notificación a Discord', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'task_id' => $taskData['task_id'],
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error al enviar notificación a Discord', [
                'error' => $e->getMessage(),
                'task_id' => $taskData['task_id'],
            ]);
        }
    }
}

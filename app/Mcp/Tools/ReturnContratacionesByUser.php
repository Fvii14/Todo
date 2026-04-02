<?php

namespace App\Mcp\Tools;

use App\Models\Ayuda;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ReturnContratacionesByUser extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Return contrataciones by user id.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $userId = $request->get('id');

        if (! $userId) {
            return Response::text('User ID is required.');
        }

        $user = User::find($userId);

        if (! $user) {
            return Response::text("User with id {$userId} not found.");
        }

        $contrataciones = Ayuda::whereIn('id', $user->contrataciones->pluck('ayuda_id'))->pluck('nombre_ayuda');

        return Response::text($contrataciones);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()
                ->description('The id of the user.')
                ->required(),
        ];
    }
}

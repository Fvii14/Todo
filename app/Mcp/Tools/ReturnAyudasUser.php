<?php

namespace App\Mcp\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ReturnAyudasUser extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Return ayudas solicitadas of user.
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

        return Response::text($user->email);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, mixed>
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

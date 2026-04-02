<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\ReturnAyudasUser;
use App\Mcp\Tools\ReturnContratacionesByUser;
use Laravel\Mcp\Server;

class N8NServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'n8n Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '0.0.1';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        This server provides informative user's detection to return to n8n.
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        ReturnAyudasUser::class,
        ReturnContratacionesByUser::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        ReturnAyudasUser::class,
        ReturnContratacionesByUser::class,
    ];
}

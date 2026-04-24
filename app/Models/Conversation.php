<?php

namespace Models;

class Conversation extends BaseModel
{
    protected static string $table      = 'chat_conversations';
    protected static string $primaryKey = 'conversation_id';
}
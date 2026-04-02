<?php

namespace App\Enums;

enum QuestionnaireTipo: string
{
    case PRE = 'pre';
    case POST = 'post';
    case CONVIVIENTE = 'conviviente';
    case ARRENDATARIO = 'arrendatario';
    case COLLECTOR = 'collector';
    case SOLICITUD = 'solicitud';
}

<?php

namespace App\Helpers;

class AnswerNormalizer
{
    /**
     * Normaliza una respuesta según el tipo de campo
     */
    public static function normalize($answerRaw, $type, $options = [])
    {
        if ($type === 'select') {
            if ($answerRaw !== null && $answerRaw !== '') {
                $found = array_search($answerRaw, $options, true);
                if ($found !== false) {
                    return (string) $found;
                }
            }

            return '';
        } elseif ($type === 'boolean') {
            return $answerRaw !== null && $answerRaw !== '' ? (string) $answerRaw : '';
        } elseif ($type === 'multiple') {
            // Decodificar si es string JSON
            if (is_string($answerRaw)) {
                $decoded = json_decode($answerRaw, true);
                $answerRaw = is_array($decoded) ? $decoded : [];
            }

            // Si no es un array, retornar array vacío
            if (! is_array($answerRaw)) {
                return [];
            }

            // Convertir textos de opciones a índices numéricos
            $selectedIndices = [];
            foreach ($answerRaw as $item) {
                if (is_numeric($item) && isset($options[$item])) {
                    // Ya es un índice numérico válido
                    $selectedIndices[] = (int) $item;
                } else {
                    // Es un texto, buscar su índice en las opciones
                    $key = array_search($item, $options, true);
                    if ($key !== false) {
                        $selectedIndices[] = (int) $key;
                    }
                }
            }

            return $selectedIndices;
        } else {
            return $answerRaw;
        }
    }
}

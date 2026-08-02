<?php

declare(strict_types=1);

namespace Senndo;

/**
 * Un fichier à téléverser sur `POST /v1/wa-media`.
 *
 * LE CORPS MULTIPART EST CONSTRUIT PAR LE SDK, et non par `CURLFile`. `CURLFile` impose que le
 * contenu vive dans un fichier sur disque : un octet-stream déjà en mémoire — issu d'une base, d'un
 * S3, d'un formulaire — devrait d'abord être écrit dans un fichier temporaire, avec tout ce que
 * cela suppose de nettoyage et de permissions. Des octets bruts plus un nom suffisent, et
 * fonctionnent aussi bien derrière un transport qui n'est pas cURL.
 */
final class MultipartUpload
{
    /**
     * @param string $file        Le contenu binaire du fichier.
     * @param string $fileName    Le nom transmis au serveur — il détermine l'extension vérifiée
     *                            contre le type MIME réel.
     * @param string $contentType Le type MIME. À défaut, `application/octet-stream`, que le serveur
     *                            refuse en 415.
     */
    public function __construct(
        public readonly string $file,
        public readonly string $fileName,
        public readonly string $contentType = 'application/octet-stream',
    ) {
    }
}

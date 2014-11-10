<?php namepsace Twitter\Connections\Exceptions;

/**
 * Thrown when user tries to upload more than the value of MAX_MEDIA_IDS (in Config.json).
 */
class MediaUploadLimitException extends \Exception {

}

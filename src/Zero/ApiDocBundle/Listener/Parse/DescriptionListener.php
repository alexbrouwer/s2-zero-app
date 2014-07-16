<?php


namespace Zero\ApiDocBundle\Listener\Parse;

use Zero\ApiDocBundle\Event\ExtractorEvent;
use Zero\ApiDocBundle\Util\DocCommentExtractor;

class DescriptionListener
{
    /**
     * Post handle
     *
     * @param ExtractorEvent $event
     */
    public function onExtractorParse(ExtractorEvent $event)
    {
        $container = $event->getContainer();

        if ($container->getDescription() === null) {
            $comments = explode(PHP_EOL, $container->getDocumentation());

            // just set the first line
            $comment = trim($comments[0]);
            $comment = preg_replace("#\n+#", ' ', $comment);
            $comment = preg_replace('#\s+#', ' ', $comment);
            $comment = preg_replace('#[_`*]+#', '', $comment);

            if ('@' !== substr($comment, 0, 1)) {
                $container->setDescription($comment);
            }
        }
    }
}
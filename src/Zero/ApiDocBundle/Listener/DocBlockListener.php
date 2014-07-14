<?php


namespace Zero\ApiDocBundle\Listener;

use Zero\ApiDocBundle\Event\ExtractorEvent;
use Zero\ApiDocBundle\Util\DocCommentExtractor;

class DocBlockListener
{

    /**
     * @var DocCommentExtractor
     */
    private $commentExtractor;

    /**
     * @param DocCommentExtractor $commentExtractor
     */
    public function __construct(DocCommentExtractor $commentExtractor)
    {
        $this->commentExtractor = $commentExtractor;
    }

    /**
     * Handle
     *
     * @param ExtractorEvent $event
     */
    public function onExtractorHandle(ExtractorEvent $event)
    {
        $container = $event->getContainer();
        $method    = $event->getMethod();

        $documentation = $this->commentExtractor->getDocCommentText($method);
        $container->setDocumentation($documentation);
    }

    /**
     * Post handle
     *
     * @param ExtractorEvent $event
     */
    public function onExtractorPostHandle(ExtractorEvent $event)
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
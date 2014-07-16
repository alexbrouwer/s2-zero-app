<?php


namespace Zero\ApiDocBundle\Listener\Handle;

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
        $route     = $event->getRoute();

        $documentation = $this->commentExtractor->getDocCommentText($method);
        $container->setDocumentation($documentation);

        $paramDocs = array();
        foreach (explode("\n", $this->commentExtractor->getDocComment($method)) as $line) {
            if (preg_match('{^@param (.+)}', trim($line), $matches)) {
                $paramDocs[] = $matches[1];
            }
            if (preg_match('{^@deprecated\b(.*)}', trim($line), $matches)) {
                $container->setDeprecated(true);
            }
            if (preg_match('{^@link\b(.*)}', trim($line), $matches)) {
                $container->setLink($matches[1]);
            }
        }

        $regexp = '{(\w*) *\$%s\b *(.*)}i';
        $requirements = $container->getRequirements();
        foreach ($route->compile()->getVariables() as $var) {
            $found = false;
            foreach ($paramDocs as $paramDoc) {
                if (preg_match(sprintf($regexp, preg_quote($var)), $paramDoc, $matches)) {
                    $requirements[$var]['dataType']    = isset($matches[1]) ? $matches[1] : '';
                    $requirements[$var]['description'] = $matches[2];

                    if (!isset($requirements[$var]['requirement'])) {
                        $requirements[$var]['requirement'] = '';
                    }

                    $found = true;
                    break;
                }
            }

            if (!isset($requirements[$var]) && false === $found) {
                $requirements[$var] = array('requirement' => '', 'dataType' => '', 'description' => '');
            }
        }

        foreach ($requirements as $name => $options) {
            $container->addRequirement($name, $options);
        }
    }
}
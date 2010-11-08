<?php

namespace Bundle\LichessBundle\Translation\Loader;

use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;

class YamlLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $catalogue = new MessageCatalogue($locale);
        $messages = Yaml::load($resource);
        $catalogue->addMessages($messages, $domain);
        $catalogue->addResource(new FileResource($resource));

        return $catalogue;
    }
}

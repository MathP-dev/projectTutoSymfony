<?php

namespace App\Form;

use App\Entity\Recipe;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

class FormListenerFactory
{

    public function __construct(private SluggerInterface $slugger) {

    }


    public function autoSlug(string $field): callable 
    {
        return function (PreSubmitEvent $event) use ($field) {
            $data = $event->getData();
            if (empty($data['slug'])) {
                $data['slug'] = strtolower($this->slugger->slug($data[$field]));
                $event->setData($data);
            }
        };
    }

    public function attachTimestamp(): callable
    {
        return function (PostSubmitEvent $event) {
            $recipe = $event->getData();

            $recipe->setUpdatedAt(new \DateTimeImmutable());
            if (!$recipe->getId()) {
                $recipe->setCreatedAt(new \DateTimeImmutable());
            }
        };
    }

}
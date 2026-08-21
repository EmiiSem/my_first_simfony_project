<?php

namespace App\Form\DataTransformer;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class TagTransformer implements DataTransformerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TagRepository $tagRepository,
    ) {
    }

    /**
     * @param Collection<int, Tag>|null $tags
     */
    public function transform($tags): string
    {
        if (null === $tags || $tags->isEmpty()) {
            return '';
        }

        $names = [];
        foreach ($tags as $tag) {
            $names[] = $tag->getName();
        }

        return implode(', ', $names);
    }

    /**
     * @return Collection<int, Tag>
     */
    public function reverseTransform($tagString): Collection
    {
        if (!$tagString) {
            return new ArrayCollection();
        }

        $names = array_unique(array_filter(array_map('trim', explode(',', (string) $tagString))));
        $tags = new ArrayCollection();

        foreach ($names as $name) {
            $tag = $this->tagRepository->findOneBy(['name' => $name]);

            if (!$tag) {
                $tag = (new Tag())->setName($name);
                $this->entityManager->persist($tag);
            }

            $tags->add($tag);
        }

        return $tags;
    }
}

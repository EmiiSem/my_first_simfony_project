<?php

namespace App\Form\DataTransformer;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

readonly class TagTransformer implements DataTransformerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TagRepository $tagRepository,
    ) {
    }

    /**
     * @param Collection<int, Tag>|null $value
     */
    public function transform($value): string
    {
        if (null === $value || $value->isEmpty()) {
            return '';
        }

        $names = [];
        foreach ($value as $tag) {
            $names[] = $tag->getName();
        }

        return implode(', ', $names);
    }

    /**
     * @return Collection<int, Tag>
     */
    public function reverseTransform($value): Collection
    {
        if (!$value) {
            return new ArrayCollection();
        }

        $items = array_unique(array_filter(array_map('trim', explode(',', $value))));
        $tags = new ArrayCollection();

        foreach ($items as $item) {
            // findBy() возвращает массив, findOneBy() - один объект или null
            $tag = $this->tagRepository->findOneBy(['name' => $item]);

            if (!$tag) {
                $tag = (new Tag())->setName($item);
                // без persist Doctrine не сохранит Tag и id останется null
                $this->entityManager->persist($tag);
            }

            $tags->add($tag);
        }

        return $tags;
    }
}

<?php

namespace App\Form;

use App\Entity\Blog;
use App\Entity\Category;
use App\Form\DataTransformer\TagTransformer;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogType extends AbstractType
{
    public function __construct(private readonly TagTransformer $transformer)
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'help' => 'Заполните заголовок блога'
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
            ])
            ->add('text', TextareaType::class, [
                'required' => true,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'query_builder' => function (CategoryRepository $repository) {
                    return $repository->createQueryBuilder('p')->orderBy('p.name', 'ASC');
                },
                'choice_label' => 'name',
                'required' => false,
                'empty_data' => null,
                'placeholder' => '-- выбор категории --',
            ])
            ->add('tags', TextType::class, [
                'label' => 'Теги',
                'required' => false,
            ])
        ;

        $builder->get('tags')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}

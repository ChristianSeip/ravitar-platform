<?php

namespace App\Domain\Blog\Form;

use App\Domain\Blog\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('message', TextareaType::class, [
			'label' => false,
			'attr' => ['placeholder' => 'Dein Kommentar...'],
		]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Comment::class,
			'csrf_protection' => true,
		]);
	}
}
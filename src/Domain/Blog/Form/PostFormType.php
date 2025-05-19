<?php

namespace App\Domain\Blog\Form;

use App\Domain\Blog\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('createdAt', DateTimeType::class, [
				'label' => 'Datum'
			])
			->add('title', TextType::class, [
				'label' => 'Title'
			])
			->add('content', TextareaType::class, [
				'label' => 'Content (Markdown)',
				'attr'  => ['rows' => 15]
			])
			->add('excerpt', TextareaType::class, [
				'label' => 'Einleitung',
				'attr'  => ['rows' => 5]
			])
			->add('tags', TextType::class, [
				'mapped'   => false,
				'required' => false,
				'label'    => 'Tags (comma-separated)',
				'help'     => 'Separate multiple tags with commas'
			])
			->add('gridRows', NumberType::class, [
				'label' => 'Grid Rows (Startseite)',
				'attr'  => [
					'min' => 1,
					'max' => 3,
				]
			])
			->add('gridCols', NumberType::class, [
				'label' => 'Grid Cols (Startseite)',
				'attr'  => [
					'min' => 1,
					'max' => 3,
				]
			])
			->add('featuredImage', FileType::class, [
				'label'    => 'Featured Image (optional)',
				'mapped'   => false,
				'required' => false,
				'help'     => 'Upload a header image for this post',
				'attr'     => [
					'accept' => '.jpg,.jpeg,.png,.webp'
				]
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Post::class,
		]);
	}
}
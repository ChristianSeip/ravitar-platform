<?php

namespace App\Domain\User\Form;

use App\Domain\User\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
						->add('username', TextType::class, [
							'label' => 'user.registration.form.label.username',
							'required' => true,
							'constraints' => [
								new NotBlank([
									'message' => 'user.registration.form.hint.username.not_blank',
								]),
								new Length([
									'min' => 3,
									'max' => 20,
									'minMessage' => 'user.registration.form.hint.username.min_length',
									'maxMessage' => 'user.registration.form.hint.username.max_length',
								])
							]
						])
            ->add('email', EmailType::class, [
								'label' => 'user.registration.form.label.email',
								'required' => true,
								'constraints' => [
									new NotBlank([
										'message' => 'user.registration.form.hint.email.not_blank',
									])
								]
						])
            ->add('plainPassword', PasswordType::class, [
								'label' => 'user.registration.form.label.password',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
											'message' => 'user.registration.form.hint.password.not_blank',
                    ]),
                    new Length([
                        'min' => 6,
                        'max' => 4096,
												'minMessage' => 'user.registration.form.hint.password.min_length',
												'maxMessage' => 'user.registration.form.hint.password.max_length',
                    ]),
                ],
            ])
						->add('dob', DateType::class, [
							'label'              => 'user.registration.form.label.dob',
							'widget'             => 'single_text',
							'error_bubbling' 		 => false,
							'html5'              => true,
							'required'           => true,
							'property_path'      => 'profile.dob',
							'constraints'        => [
								new NotBlank([
									'message' => 'user.registration.form.hint.dob.not_blank',
								]),
								new LessThanOrEqual([
									'value'   => (new \DateTimeImmutable())->sub(new \DateInterval('P14Y')),
									'message' => 'user.registration.form.hint.dob.min_age',
								]),
								new GreaterThanOrEqual([
									'value'   => (new \DateTimeImmutable())->sub(new \DateInterval('P100Y')),
									'message' => 'user.registration.form.hint.dob.max_age',
								]),
							],
						])
						->add('agreeTerms', CheckboxType::class, [
							'label' => 'user.registration.form.label.terms',
							'mapped' => false,
							'label_html' => true,
							'constraints' => [
								new IsTrue([
									'message' => 'user.registration.form.hint.terms.required',
								]),
							],
						])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
						'translation_domain' => 'messages',
        ]);
    }
}

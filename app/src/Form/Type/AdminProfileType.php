<?php
/**
 * Admin profile type.
 */

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class AdminProfileType.
 */
class AdminProfileType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'email',
            EmailType::class,
            [
                'label' => 'label.email',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'form.email.not_blank',
                    ]),
                    new Email([
                        'message' => 'form.email.invalid',
                    ]),
                ],
            ]
        );

        $builder->add(
            'plainPassword',
            PasswordType::class,
            [
                'label' => 'label.new_password',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'form.password.min_length',
                        'max' => 4096,
                    ]),
                ],
            ]
        );

        $builder->add(
            'confirmPassword',
            PasswordType::class,
            [
                'label' => 'label.confirm_password',
                'required' => false,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
            ]
        );
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    /**
     * Returns block prefix for the form.
     *
     * @return string The prefix
     */
    public function getBlockPrefix(): string
    {
        return 'admin_profile';
    }
}

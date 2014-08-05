<?php
namespace Module\Feedback\Form;

use Zend\InputFilter\InputFilter;

class FeedbackFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name'        => 'username',
            'required'    => true,
            'filters'     => array(
                array(
                    'name'    => 'StringTrim',
                ),
            ),
            'validators'  => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'min'     => 5,
                        'max'     => 25,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name'        => 'email',
            'required'    => true,
            'filters'     => array(
                array(
                    'name'    => 'StringTrim',
                ),
            ),
            'validators'  => array(
                array(
                    'name' => 'EmailAddress',
                ),
            ),
        ));

        $this->add(array(
                'name'        => 'content',
                'required'    => true,
                'filters'     => array(
                        array(
                                'name'    => 'StringTrim',
                        ),
                ),
        ));
    }
}
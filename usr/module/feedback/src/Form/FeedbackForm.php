<?php
namespace Module\Feedback\Form;

use Pi\Form\Form as BaseForm;

class FeedbackForm extends BaseForm
{
    public function init()
    {
        $this->add(array(
            'name'          => 'username',
            'options'       => array(
                'label'     => __('Username'),
            ),
            'attributes'    => array(
                'type'      => 'text',
                'size'      => '30',
            ),
        ));

        $this->add(array(
            'name'          => 'email',
            'options'       => array(
                'label'     => __('Email'),
            ),
            'attributes'    => array(
                'type'      => 'text',
                'size'      => '30',
            ),
        ));

        $this->add(array(
            'name'          => 'content',
            'options'       => array(
                    'label'     => __('Feedback Message'),
            ),
            'attributes'    => array(
                    'type'      => 'textarea',
                    'cols'      => 50,
                    'rows'      => 5,
            ),
        ));

        $this->add(array(
            'name'          => 'submit',
            'attributes'    => array(
                'value'     => __('Submit'),
            ),
            'type'          => 'submit',
        ));
    }
}
<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Feedback\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Feedback\Form\FeedbackForm;
use Module\Feedback\Form\FeedbackFilter;

use Pi\Application\Api\AbstractApi;
use Pi\Db\Sql\Where;
use Pi\Db\RowGateway\RowGateway;

/**
 * Feedback post controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class IndexController extends ActionController
{
    /**
     * Post feedback
     *
     * @return string
     */
    public function indexAction()
    {
        $form = $this->renderForm();

        $currentUser    = Pi::service('user')->getUser();

        $this->view()->assign('title', __('Feedback Post'));
        $this->view()->assign('user', $currentUser);
        $this->view()->assign('form', $form);

        $this->view()->setTemplate('feedback-index');
    }

    /**
     * Render Feedback Form
     *
     * @return string
     */
    protected function renderForm()
    {
        $form = new FeedbackForm('Feedback');
        $form->setAttribute('action', $this->url('', array('action' => '')));

        return $form;
    }

    /**
     * FeedBack submit action.
     *
     * @return string
     */
    public function postAction()
    {
        $form = $this->renderForm();

        $this->view()->assign('title', __('Feedback Post'));

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new FeedbackFilter);
            if (!$form->isValid()) {
                $this->view()->assign('form', $form);
                return ;
            }
            $data = $form->getData();

//             print('<pre>' . print_r($data, TRUE) . '</pre>');
            $rowId = $this->_saveFeedback($data);

            $complete = 0;

            if (!empty($rowId)) {
                $complete = 1;
                $form = null;
            }


            $this->view()->setTemplate(false);
            $this->view()->assign('result',
                array(
                    'complete' => $complete,
                    'data' => $data,
                    'rowid' => $rowId,
                    'form'          => $form,
                )
            );
            $this->view()->assign('content', __('Success!'));

            return ;
        }

        $currentUser    = Pi::service('user')->getUser();

        $this->view()->assign('user', $currentUser);
        $this->view()->assign('form', $form);

//         $this->view()->setTemplate('feedback-index');
    }

    /**
     * Save feedback into DB.
     *
     * @param array $data
     *
     * @return string
     *   - Row id.
     */
    protected function _saveFeedback($data) {

        if (isset($data['submit'])) unset($data['submit']);

        $currentUser    = Pi::service('user')->getUser();
        $data['uid'] = ($currentUser->get('id')) ? $currentUser->get('id') : '0';

        if (!isset($data['time'])) {
            $data['time'] = time();
        }
        $data['ip']   = Pi::user()->getIp();

        $model = Pi::model('post', 'feedback');
        $row = $model->createRow($data);

        try {
            $row->save();
        } catch (\Exception $e) {
            print('Error info:<pre>' . print_r($e->getMessage(), TRUE) . '</pre>');
            return false;
        }

        return (int) $row['id'];
    }


}

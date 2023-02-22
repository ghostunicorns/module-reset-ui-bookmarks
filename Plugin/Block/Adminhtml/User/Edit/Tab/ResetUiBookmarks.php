<?php
declare(strict_types=1);

namespace GhostUnicorns\ResetUiBookmarks\Plugin\Block\Adminhtml\User\Edit\Tab;

use Closure;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Block\User\Edit\Tab\Main;

class ResetUiBookmarks
{
    /**
     * @param Main $subject
     * @param Closure $proceed
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundGetFormHtml(
        Main    $subject,
        Closure $proceed
    ) {
        $form = $subject->getForm();
        if (is_object($form)) {
            $fieldset = $form->getElement('base_fieldset');
            $userId = 0;

            foreach ($fieldset->getElements() as $element) {

                if ($element->getId() === 'user_id') {
                     $userId = $element->getValue();
                }
            }

            $fieldset = $form->addFieldset('gu_resetuibookmarks', ['legend' => __('Bookmarks')]);

            $fieldset->addField(
                'reset_ui_bookmarks',
                'label',
                [
                    'container_id' => 'reset_ui_bookmarks',
                    'after_element_html' => $subject->getLayout()
                        ->getBlock('gu.resetuibookmarks.system.account')
                        ->setData('userId', $userId)
                        ->toHtml()
                ]
            );
            
            $subject->setForm($form);
        }

        return $proceed();
    }
}

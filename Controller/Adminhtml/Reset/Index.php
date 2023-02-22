<?php
declare(strict_types=1);

namespace GhostUnicorns\ResetUiBookmarks\Controller\Adminhtml\Reset;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Ui\Model\ResourceModel\BookmarkRepository;
use Magento\User\Model\UserFactory;

class Index extends Action
{
    /**
     * @var BookmarkInterfaceFactory
     */
    protected $bookmarkFactory;

    /**
     * @var BookmarkRepository
     */
    protected $bookmarkRepository;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param BookmarkInterfaceFactory $bookmarkFactory
     * @param BookmarkRepository $bookmarkRepository
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param UserFactory $userFactory
     */
    public function __construct(
        Context $context,
        BookmarkInterfaceFactory $bookmarkFactory,
        BookmarkRepository $bookmarkRepository,
        ResultFactory $resultFactory,
        RequestInterface $request,
        UserFactory $userFactory
    ) {
        parent::__construct($context);

        $this->bookmarkFactory = $bookmarkFactory;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->userFactory = $userFactory;
    }

    /**
     * Execute the controller.
     *
     * @return mixed
     */
    public function execute()
    {
        $system = true;
        $params = $this->request->getParam('gu_resetuibookmarks');

        $userId = $this->_auth->getUser()->getId();


        if (!empty($params['userId'])) {

            $system = false;
            $userId = $params['userId'];
        }

        $user = $this->userFactory->create()->load($userId);

        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('adminhtml/user/edit', ['user_id' => $userId]);

        if ($system) {
            $redirect->setPath('adminhtml/system_account/index');
        }

        try {
            $collection = $this->bookmarkFactory->create()->getCollection();
            $collection->addFieldToFilter('user_id', ['eq' => $userId]);

            switch ($params['identifier']) {
                case 'saved-only':
                    $collection->addFieldToFilter('identifier', ['like' => '_%']);
                    break;
                case 'saved-exclude':
                    $collection->addFieldToFilter('identifier', ['in' => ['current','default']]);
                    break;
            }

            $message = __('No UI Bookmarks found for user (%1).', $user->getEmail());

            if (!empty($collection->getItems())) {

                foreach ($collection->getItems() as $bookmark) {
                    $this->bookmarkRepository->deleteById($bookmark->getBookmarkId());
                }

                $message = __('The UI Bookmarks for user (%1) have been cleared successfully.', $user->getEmail());

                if ($system) {
                    $message = __('Your UI Bookmarks were cleared successfully.');
                }
            }

            $this->messageManager->addSuccessMessage($message);

            return $redirect;
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('We were unable to submit your request. Please try again.'));

            return $redirect;
        }
    }
}

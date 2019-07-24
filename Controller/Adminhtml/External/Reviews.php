<?php

namespace Yotpo\Yotpo\Controller\Adminhtml\External;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Yotpo\Yotpo\Model\Config as YotpoConfig;

class Reviews extends \Magento\Backend\App\Action
{
    /**
     * initialize:
     */
    private $scope;
    private $scopeId;
    private $isEnabled;
    private $appKey;
    private $isAppKeyAndSecretSet;

    /**
     * @var YotpoConfig
     */
    private $yotpoConfig;

    /**
     * Constructor
     *
     * @param Context $context
     * @param YotpoConfig $yotpoConfig
     */
    public function __construct(
        Context $context,
        YotpoConfig $yotpoConfig
    ) {
        parent::__construct($context);
        $this->yotpoConfig = $yotpoConfig;
    }

    private function initialize()
    {
        if (($storeId = $this->getRequest()->getParam("store", 0))) {
            $this->scope = ScopeInterface::SCOPE_STORE;
            $this->scopeId = $storeId;
        } elseif (($websiteId = $this->getRequest()->getParam("website", 0))) {
            $this->scope = ScopeInterface::SCOPE_WEBSITE;
            $this->scopeId = $websiteId;
        }
        $this->isEnabled = $this->yotpoConfig->isEnabled($this->scopeId, $this->scope);
        $this->isAppKeyAndSecretSet = $this->yotpoConfig->isAppKeyAndSecretSet($this->scopeId, $this->scope);

        if (!($this->isEnabled && $this->isAppKeyAndSecretSet)) {
            $this->scope = ScopeInterface::SCOPE_STORE;
            foreach ($this->yotpoConfig->getAllStoreIds(true) as $storeId) {
                $this->scopeId = $storeId;
                $this->isEnabled = $this->yotpoConfig->isEnabled($this->scopeId, $this->scope);
                $this->isAppKeyAndSecretSet = $this->yotpoConfig->isAppKeyAndSecretSet($this->scopeId, $this->scope);
                if ($this->isEnabled && $this->isAppKeyAndSecretSet) {
                    $this->appKey = $this->yotpoConfig->getAppKey($this->scopeId, $this->scope);
                    break;
                }
            }
        }
    }

    public function execute()
    {
        $this->initialize();
        if ($this->appKey) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                ->setUrl('https://yap.yotpo.com/?utm_source=MagentoAdmin_ReportingReviews#/moderation/reviews');
        } else {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                ->setUrl('https://www.yotpo.com/integrations/magento/?utm_source=MagentoAdmin_ReportingReviews');
        }
    }
}

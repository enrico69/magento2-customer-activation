<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * Date: 02/11/2017
 */
namespace Enrico69\Magento2CustomerActivation\Model;

use Magento\Framework\Option\ArrayInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;

class EmailTemplateList implements ArrayInterface
{
    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * EmailTemplateList constructor.
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Email\Model\ResourceModel\Template\Collection
     */
    protected function getTemplatesList()
    {
        return $this->collectionFactory->create();
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $templates = $this->getTemplatesList();
        $arrayOfTemplates = [];
        $arrayOfTemplates[] = ['value' => 'enrico69_activation_email', 'label' => 'Default Email'];

        foreach ($templates as $template) {
            /** @var \Magento\Email\Model\Template $template */
            $arrayOfTemplates[] = ['value' => $template->getId(), 'label' => $template->getTemplateCode()];
        }

        return $arrayOfTemplates;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $templates = $this->getTemplatesList();
        $arrayOfTemplates = [];
        $arrayOfTemplates['enrico69_activation_email'] = 'Default Email';

        foreach ($templates as $template) {
            /** @var \Magento\Email\Model\Template $template */
            $arrayOfTemplates[$template->getId()] = $template->getTemplateCode();
        }

        return $arrayOfTemplates;
    }
}

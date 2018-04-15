<?php
/**
 * This class is the complete original Magento class
 * + the official magento fix in commit 7759827ff277157d3cf1ae93b1392a73bc2538de.
 *
 * @author Magento
 * Date: 15/04/2018
 */
namespace Enrico69\Magento2CustomerActivation\Indexer\Handler;

use Magento\Framework\Indexer\Handler\AttributeHandler as OriginalHandler;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;

class AttributeHandler extends OriginalHandler
{
    /**
     * Prepare SQL for field and add it to collection
     *
     * @param SourceProviderInterface $source
     * @param string $alias
     * @param array $fieldInfo
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareSql(SourceProviderInterface $source, $alias, $fieldInfo)
    {
        if (isset($fieldInfo['bind'])) {
            if (!method_exists($source, 'joinAttribute')) {
                return;
            }

            $source->joinAttribute(
                $fieldInfo['name'],
                $fieldInfo['entity'] . '/' . $fieldInfo['origin'],
                $fieldInfo['bind'],
                null,
                'left'
            );
        } else {
            $source->addFieldToSelect($fieldInfo['origin'], 'left');
        }
    }
}

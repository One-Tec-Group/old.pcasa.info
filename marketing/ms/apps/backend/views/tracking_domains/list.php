<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright 2013-2018 MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.6
 */

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderContent} to false 
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) {
    /**
     * @since 1.3.9.2
     */
    $itemsCount = TrackingDomain::model()->count();
    ?>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-flash') . $pageHeading . '</h3>')
                    ->render();
                ?>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->addIf($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $domain, 'columns' => array('customer_id', 'name', 'scheme', 'date_added', 'last_updated')), true), $itemsCount)
                    ->add(HtmlHelper::accessLink(IconHelper::make('create') . Yii::t('app', 'Create new'), array('tracking_domains/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))))
                    ->add(HtmlHelper::accessLink(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('tracking_domains/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh'))))
                    ->add(CHtml::link(IconHelper::make('info'), '#page-info', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal')))
                    ->render();
                ?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
            <?php 
            /**
             * This hook gives a chance to prepend content or to replace the default grid view content with a custom content.
             * Please note that from inside the action callback you can access all the controller view
             * variables via {@CAttributeCollection $collection->controller->data}
             * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderGrid} to false 
             * in order to stop rendering the default content.
             * @since 1.3.3.1
             */
            $hooks->doAction('before_grid_view', $collection = new CAttributeCollection(array(
                'controller'  => $this,
                'renderGrid'  => true,
            )));

            /**
             * This widget renders default getting started page for this particular section.
             * @since 1.3.9.2
             */
            $this->widget('common.components.web.widgets.StartPagesWidget', array(
                'collection' => $collection,
                'enabled'    => !$itemsCount,
            ));
            
            // and render if allowed
            if ($collection->renderGrid) {
                $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                    'ajaxUrl'           => $this->createUrl($this->route),
                    'id'                => $domain->modelName.'-grid',
                    'dataProvider'      => $domain->search(),
                    'filter'            => $domain,
                    'filterPosition'    => 'body',
                    'filterCssClass'    => 'grid-filter-cell',
                    'itemsCssClass'     => 'table table-hover',
                    'selectableRows'    => 0,
                    'enableSorting'     => false,
                    'cssFile'           => false,
                    'pagerCssClass'     => 'pagination pull-right',
                    'pager'             => array(
                        'class'         => 'CLinkPager',
                        'cssFile'       => false,
                        'header'        => false,
                        'htmlOptions'   => array('class' => 'pagination')
                    ),
                    'columns' => $hooks->applyFilters('grid_view_columns', array(
                        array(
                            'name'  => 'customer_id',
                            'value' => '!empty($data->customer) ? $data->customer->getFullName() : Yii::t("app", "System")',
                            'filter'=> CHtml::activeTextField($domain, 'customer_id'),
                        ),
                        array(
                            'name'  => 'name',
                            'value' => 'HtmlHelper::accessLink($data->name, array("tracking_domains/update", "id" => $data->domain_id), array("fallbackText" => true))',
                            'type'  => 'raw',
                            'filter'=> CHtml::activeTextField($domain, 'name'),
                        ),
                        array(
                            'name'  => 'scheme',
                            'value' => 'strtoupper($data->scheme)',
                            'filter'=> $domain->getSchemesList(),
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'last_updated',
                            'value' => '$data->lastUpdated',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $domain->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'update' => array(
                                    'label'     => IconHelper::make('update'), 
                                    'url'       => 'Yii::app()->createUrl("tracking_domains/update", array("id" => $data->domain_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app','Update'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("tracking_domains/update")',
                                ),
                                'delete' => array(
                                    'label'     => IconHelper::make('delete'), 
                                    'url'       => 'Yii::app()->createUrl("tracking_domains/delete", array("id" => $data->domain_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app','Delete'), 'class' => 'btn btn-danger btn-flat delete'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("tracking_domains/delete")',
                                ),    
                            ),
                            'headerHtmlOptions' => array('style' => 'text-align: right'),
                            'footerHtmlOptions' => array('align' => 'right'),
                            'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                            'template'          => '{update} {delete}'
                        ),
                    ), $this),
                ), $this)); 
            }
            /**
             * This hook gives a chance to append content after the grid view content.
             * Please note that from inside the action callback you can access all the controller view
             * variables via {@CAttributeCollection $collection->controller->data}
             * @since 1.3.3.1
             */
            $hooks->doAction('after_grid_view', new CAttributeCollection(array(
                'controller'  => $this,
                'renderedGrid'=> $collection->renderGrid,
            )));
            ?>
            <div class="clearfix"><!-- --></div>
            </div>    
        </div>
    </div>
    <!-- modals -->
    <div class="modal modal-info fade" id="page-info" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                </div>
                <div class="modal-body">
                    <?php echo Yii::t('tracking_domains', 'Please note, in order for this feature to work this (sub)domain needs a dedicated IP address, otherwise all defined CNAMES for it will point to the default domain on this server.');?>
                    <br />
                    <strong><?php echo Yii::t('tracking_domains', 'If you do not use a dedicated IP address for this domain only or you are not sure you do so, do not use this feature!');?></strong>
                </div>
            </div>
        </div>
    </div>
<?php 
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.3.1
 */
$hooks->doAction('after_view_file_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));
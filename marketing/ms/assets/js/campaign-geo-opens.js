/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright 2013-2018 MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.5
 */
jQuery(document).ready(function($){

    var ajaxData = {};
    if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
        var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
        var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
        ajaxData[csrfTokenName] = csrfTokenValue;
    }

    var placeholder = $('#campaign-geo-opens');
    if (placeholder.length) {
        $.plot(placeholder, placeholder.data('chartdata'), {
            series: {
                pie: {
                    show: true,
                    label: {
                        show: false
                    },
                    stroke: {
                        width: 3
                    }
                }
            },
            legend: {
                show: true,
                labelFormatter: function(label, series) {
                    return '<div>&nbsp; ' + label + ' ( ' + series.count_formatted + ' / ' +  Math.round(series.percent) + '% ) </div>';
                }
            },
            grid: {
                hoverable: true,
                clickable: true
            }
        });    
    }
});
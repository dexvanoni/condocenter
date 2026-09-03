/**
 * Inicialização segura de DataTables.
 * Remove linhas placeholder com colspan e evita alertas de erro na UI.
 */
(function (window, $) {
    'use strict';

    if (!$ || !$.fn.dataTable) {
        return;
    }

    $.fn.dataTable.ext.errMode = 'none';

    function removePlaceholderRows($table) {
        $table.find('tbody tr').each(function () {
            const $cells = $(this).children('td');
            if ($cells.length === 1 && $cells.first().attr('colspan')) {
                $(this).remove();
            }
        });
    }

    function hasDataRows($table) {
        return $table.find('tbody tr').filter(function () {
            const $cells = $(this).children('td');
            return $cells.length > 0 && !$cells.first().attr('colspan');
        }).length > 0;
    }

    window.initSafeDataTable = function (selector, options) {
        const $table = $(selector);
        if (!$table.length) {
            return null;
        }

        removePlaceholderRows($table);

        if ($.fn.DataTable.isDataTable($table)) {
            return $table.DataTable();
        }

        const defaults = {
            language: {
                emptyTable: 'Nenhum registro encontrado.',
                zeroRecords: 'Nenhum registro encontrado para o filtro aplicado.',
            },
        };

        const config = $.extend(true, {}, defaults, options || {});

        if (!hasDataRows($table) && config.deferRender !== false) {
            config.deferRender = true;
        }

        return $table.DataTable(config);
    };
})(window, window.jQuery);

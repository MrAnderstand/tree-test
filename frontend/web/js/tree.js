$(function(){
    var selectedNode = null;
    var treeSearchTimer;
    $("#tree").dynatree({
        children: treeData,
        onClick: function(node, e) {
            selectedNode = node;
            // console.log(node.data.key);
        }
    });
    // Обработка редактирования поисковой строки
    $('body').on('paste keyup', '#search', function (e) {
        var value = $(this).val();
        if (value.length >= 3 || value.length === 0) {
            clearTimeout(treeSearchTimer);
            treeSearchTimer = setTimeout(searchSend, 500, value);
        }
    });
    
    // Добавление раздела
    $('body').on('click', '#addSection', function (e) {
        $('#nodeManipulationForm').attr('action', addSectionUrl);
        $('#nodeModal').modal('show');
    });
    // Добавление элемента
    $('body').on('click', '#addNode', function (e) {
        if (!selectedNode) {
            alert('Пожалуйста, выберите элемент дерева.');
            return;
        }
        var $form = $('#nodeManipulationForm');
        $form.attr('action', addNodeUrl);
        $form.find('input[name=id]').val(selectedNode.data.key);
        $('#nodeModal').modal('show');
    });
    // Редактирование элемента
    $('body').on('click', '#editNode', function (e) {
        if (!selectedNode) {
            alert('Пожалуйста, выберите элемент дерева.');
            return;
        }
        var $form = $('#nodeManipulationForm');
        $form.attr('action', editNodeUrl);
        $form.find('input[name=id]').val(selectedNode.data.key);
        $form.find('input[name=preload]').val(1);
        $form.data('yiiActiveForm').validated = true;
        $form.submit();
        $form.data('yiiActiveForm').validated = false;
        $('#nodeModal').modal('show');
    });
    // Удаление элемента
    $('body').on('click', '#deleteNode', function (e) {
        if (!selectedNode) {
            alert('Пожалуйста, выберите элемент дерева.');
            return;
        }
        if (confirm('Объект будет удален вместе с дочерними объектами. Удаленные объекты нельзя восстановить. Продолжить?')) {
            deleteNode(selectedNode);
        }
    });
    
    // Обработка дерева после ответа AJAX-запроса
    $("#nodeManipulation").on('pjax:beforeReplace', function() {
        if ($('#nodeManipulationForm [name=preload]').val() === '') {
            return false;
        }
    }).on('pjax:complete', function(event, textStatus) {
        try {
            var obj = JSON.parse(textStatus.responseText);
            switch (obj.action) {
                case 'addSection':
                    var rootNode = $("#tree").dynatree("getRoot");
                    rootNode.addChild(obj.data);
                    break;
                case 'addNode':
                    selectedNode.fromDict({ isFolder: true });
                    var child = selectedNode.addChild(obj.data);
                    selectedNode.expand(true);
                    break;
                case 'editNode':
                    selectedNode.fromDict(obj.data);
                    break;
            }
            $('#nodeModal').modal('hide');
            resetForm($('#nodeManipulationForm'));
        } catch(e) {
            
        }
    }).on('pjax:error', function(event) {
        alert('Во время передачи данных произошла ошибка.');
        event.preventDefault();
    });
    
    // Управление модальным окном
    $('#nodeModal').on('click', '.cancel', function (e) {
        $('#nodeModal').modal('hide');
        resetForm($('#nodeManipulationForm'));
    });
});

/** @type {mixed} jqXHR объект для сброса предыдущего запроса */
var treeSearchRequest = null;
/**
 * Отправка поисковой строки и получение нового дерева
 * @param  {string} value Поисковая строка
 */
function searchSend(value) {
    if (treeSearchRequest !== null) {
        treeSearchRequest.abort();
        treeSearchRequest = null;
    }
    treeSearchRequest = $.ajax({
        method: "POST",
        url: treeSearchUrl,
        data: { search: value },
        dataType: 'JSON'
    }).done(function(treeData) {
        $("#tree")
            .dynatree("option", "children", treeData)
            .dynatree("getTree").reload();
    });
}

/**
 * Удаляет элемент вместе со всеми дочерними
 * @param  {mixed} node Элемент дерева
 */
function deleteNode(node) {
    $.ajax({
        method: "POST",
        url: deleteNodeUrl,
        data: { id: node.data.key },
        dataType: 'JSON'
    }).done(function(res) {
        node.remove();
    });
}

/**
 * Очищает форму, подготавливает для повторного использования
 * @param  {jQueryObj} $form Форма
 */
function resetForm($form) {
    $form.trigger('reset');
    $form.find('input').val('');
    $form.data('yiiActiveForm').submitting = false;
    $form.data('yiiActiveForm').validated = false;
}
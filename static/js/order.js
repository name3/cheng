
$(function () {

    // 订单推进
    $('.next-btn').click(function () {
        var that = $(this);
        var action = that.data('action');
        var id = that.parents('entry').data('id');
        $.get(
            '/order',
            {
                a: action,
                id: id
            },
            function (ret) {
                console.log('ok');
                that.data('action', ret.action).text(ret.caption);
            }, 'json');
    });

    // 选择工厂 弹出框
    $('.choose-factory-btn').click(function () {
        var id = $(this).parents('.entry').data('id');
        $('.append-parent').show()
            .find('.factory-select.append-div').data('id', id).show();
    });

    // 选择工厂 确认按钮
    $('.factory-select.append-div form').submit(function (e) {
        var that = $(this);
        var facotoryId = that.find('select').val();
        var div = that.parents('.append-div');
        var orderId = div.data('id');
        $.get(
            '/order',
            {
                a: 'change_factory',
                factory_id: facotoryId,
                order_id: orderId
            },
            function (ret) {
                console.log('ok');
                div.hide().parents('.append-parent').hide();
                $('.entry[data-id=' + orderId + '] .factory-name .text').text(ret);
            });
        return false;
    });
});
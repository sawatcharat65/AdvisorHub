$(document).ready(function() {
    // จัดการการคลิกปุ่มสถานะ
    $('.topic-status button').on('click', function() {
        $('.topic-status button').removeClass('active');
        $(this).addClass('active');
        const section = $(this).data('section');
        $('.topic-section').removeClass('active');
        $(`.topic-section[data-section="${section}"]`).addClass('active');
    });

    // จัดการเมนูดรอปดาวน์
    $(document).on('click', '.menu-button', function() {
        const $menuContainer = $(this).closest('.menu-container');
        const $dropdownMenu = $menuContainer.find('.dropdown-menu');
        $dropdownMenu.toggleClass('active');
        $('.dropdown-menu.active').not($dropdownMenu).removeClass('active');
    });

    $(document).on('click', function(event) {
        if (!$(event.target).closest('.menu-container').length) {
            $('.dropdown-menu.active').removeClass('active');
        }
    });

    // จัดการการลบ
    $(document).on('click', '.delete-button', function() {
        const title = $(this).data('title');
        const $container = $(this).closest('.message-container');
        const type = $container.data('type');
        const currentCount = $container.find('.message').length;

        if (confirm('Are you sure you want to delete this topic?')) {
            $.ajax({
                url: 'delete_message.php',
                method: 'POST',
                data: { title: title, receiver_id: receiverId },
                success: function(response) {
                    if (response === 'success') {
                        $.ajax({
                            url: 'view_more_messages.php',
                            method: 'POST',
                            data: {
                                type: type,
                                offset: 0,
                                limit: currentCount,
                                receiver_id: receiverId,
                                get_total: true
                            },
                            dataType: 'json',
                            success: function(data) {
                                $container.empty().append(data.messages);
                                const newTotal = data.total;
                                const newCount = $container.find('.message').length;
                                const $viewMore = $container.find('.view-more');
                                if (newTotal > newCount) {
                                    if ($viewMore.length) {
                                        $viewMore.data('total', newTotal);
                                        $viewMore.data('count', newCount);
                                    } else {
                                        $container.append(
                                            `<button class="view-more" data-type="${type}" data-count="${newCount}" data-total="${newTotal}">View More</button>`
                                        );
                                    }
                                } else if ($viewMore.length) {
                                    $viewMore.remove();
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("AJAX Error: ", status, error);
                            }
                        });
                    } else {
                        alert('Failed to delete the topic.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", status, error);
                    alert('An error occurred while deleting the topic.');
                }
            });
        }
    });

    // จัดการปุ่ม View More
    $(document).on('click', '.view-more', function() {
        const $button = $(this);
        const type = $button.data('type');
        const count = parseInt($button.data('count'));
        const total = parseInt($button.data('total'));

        $.ajax({
            url: 'view_more_messages.php',
            method: 'POST',
            data: { type: type, offset: count, limit: 5, receiver_id: receiverId },
            success: function(response) {
                $button.before(response);
                const newCount = count + 5;
                $button.data('count', newCount);
                if (newCount >= total) {
                    $button.remove();
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", status, error);
            }
        });
    });
});
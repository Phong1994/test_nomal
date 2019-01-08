$(document).ready(function () {
    fixDropdownAdminAction();
    var showDropdown = false;
    $(document).mouseup(function(e)
    {
        var container = $(".admin_action-dropdown");

        if (!container.is(e.target) && container.has(e.target).length === 0)
        {
            if(showDropdown){
                hideDropdownMenu();
            }
        }
    });
    $(document).on('click','.admin_action-dropdown',function () {
        // console.log('bt' + showDropdown)

        if(showDropdown){
            hideDropdownMenu();
        }else{
            showDropdownMenu();
        }
    });
    function animateAdminActionDropdown(start,end) {
        var $elem = $('.admin_action-dropdown');
        $({deg: start}).animate({deg: end}, {
            duration: 200,
            step: function(now) {
                $elem.css({
                    transform: 'rotate(' + now + 'deg)'
                });
            }
        },'linear',function () {
        });
    }
    function fixDropdownAdminAction() {
        var height_child = parseInt($('.admin__action').css('height'));
        var height_par = parseInt($('.admin-header-action').css('height'));
        var top = (height_par - height_child)/2
        $('.admin__action').css('margin-top',top)
    }
    function showDropdownMenu() {
        showDropdown = true;

        animateAdminActionDropdown(0,180)
        $('#admin__action-parent-dropdown').addClass('admin__action-active-dropdown');
        $('.admin__action-notify').addClass('admin__action-active-dropdown-bottom');
        $('.dropdown-menu-admin-account').show();
    }
    function hideDropdownMenu() {
        showDropdown = false;

        animateAdminActionDropdown(180,0)
        $('#admin__action-parent-dropdown').removeClass('admin__action-active-dropdown');
        $('.admin__action-notify').removeClass('admin__action-active-dropdown-bottom');
        $('.dropdown-menu-admin-account').hide();
    }
})
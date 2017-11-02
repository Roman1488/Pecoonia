
    $(document).ready(function(){

        var sub_menu = $('#nav_bar .sub'),
            dash_en = $('#dash_id').find('.enable'),
            dash_dis = $('#dash_id').find('.disable');
        // show more button if profiles is more than 2
        $('#header-trigger , #inner-header-trigger').on('click submit', function(){
           var subln = $(sub_menu).children('li:not(.more)').length;
            // change dashboard icon when portfolios number is more or null
            ( subln != 0 )? ($(dash_en).css('display','block'), $(dash_dis).css('display','none')) :
                            ($(dash_en).css('display','none'), $(dash_dis).css('display','block')); 
            // hower more button when portfolios number less then 2
            ( subln < 3 )? $(sub_menu).children('li.more').remove(): $(sub_menu).children("li:not(:nth-child(1), :nth-child(2)):not(.more)").addClass("li_closed");
        });

        // ul sublist trigger
        $('#dash_id').on('click submit', function(){
            $(this).parent('#nav_bar').find('.sub').slideToggle(); 
        });


        $('.notification_button').click(function () {
            $('#notification').addClass('visible')
        });

        $('.notification-close').click(function () {
            $('#notification').removeClass('visible')
        });

        $(document).on('click', function(e){

            if (!$(e.target).closest('.notification_button').length && !$(e.target).closest('#notification').length) {
                $('#notification').removeClass('visible');
            }

            if (!$(e.target).closest('.contact-form-inner').length && 
                !$(e.target).hasClass('contact-form-inner') && 
                !$(e.target).hasClass('email-btn') && 
                $(e.target).attr('id') != 'feedback-btn') {

                $('body').removeClass('contact-form-active');
            }

            if (!$(e.target).is(".det, .other_opt, .opt_menu_tb, .opt_trigger_tb, .opt_trigger_tb > img")) 
            {   
                if ($(e.target).closest('.card:not(.card-p)').length || $(e.target).hasClass('card:not(.card-p)')) {
                    var view_opt = ($(e.target).closest('.card').length)? $(e.target).closest('.card') : $(e.target);
                    view_opt.find('.det').trigger('click');
                } 

                if ($('.opt_trigger_tb').offsetParent().is('div.table')) $('.opt_trigger_tb').offsetParent().removeAttr('style').removeClass('opt_on');

                $('.opt_menu_tb').css("display","none");
                return;
            };

            if ($(e.target).is(".opt_trigger_tb > img"))
            {   
                    $('.options_tb .opt_menu_tb').css("display", "none");
                var trg = $(e.target).closest('tr').children('.options_tb').children('.opt_menu_tb'),
                    context = $(e.target).offsetParent(),
                    context_height = $(context).innerHeight(),
                    parent_pos = $(context).offset(),
                    child_pos = $(e.target).offset(),
                    trig_width = $('.opt_trigger_tb').outerWidth(),
                    trig_height = $('.opt_trigger_tb').innerHeight(),
                    trg_width = $(trg).outerWidth(),
                    trg_height = $(trg).innerHeight(),
                    childOffset = {
                        top:child_pos.top - parent_pos.top,
                        left:child_pos.left - parent_pos.left
                    },
                    new_opt_pos_bottom = childOffset.top + trg_height,
                    context_pos_bottom = context_height;

                if ($(context).is('div.table:not(.opt_on)') && new_opt_pos_bottom > context_pos_bottom ) {

                    $(context).animate({
                        height:trg_height * 0.8 + context_height
                    }, function(){
                        $(this).addClass('opt_on');
                    })
                } 
                
                $(trg).css({'top':childOffset.top - trig_height / 2,
                            'left':(($(context).is('div.table'))? childOffset.left - trg_width + trig_width : childOffset.left - trg_width + trig_width * 2)}).slideDown();
                
            } 

        })

});   
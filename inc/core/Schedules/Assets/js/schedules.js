"use strict";
function Schedules(){
    var self= this;
    /*var $(".sub-sidebar") = $(".sub-sidebar");
    var $(".schedules-main") = $(".schedules-main");
    var $(".schedule-list") = $(".schedule-list");
    var $("#schedule-calendar") = $("#schedule-calendar");
*/
    this.init= function(){
        self.action();
    };

    this.action = function(){

        if( $(".schedules-main").length > 0 ){
            var type = $(".sub-sidebar").find('[name="schedule_type"]:checked').val();
            var category = $(".sub-sidebar").find("input[name='schedule_of']:checked").val();
            var method = $(".sub-sidebar").find("[name='method_post']").val();
            var time = $(".sub-sidebar").find('[name="schedule_time"]').val();
            var query_id = $(".sub-sidebar").find('[name="query_id"]').val();
            query_id = parseInt(query_id);
            var query_id_str = "";
            if(Number.isInteger(query_id)){
                query_id_str = "?query_id=" + query_id;
            }

            var d =new Date(time);

            $("#schedule-calendar").monthly({
                mode: 'event',
                dataType: 'json',
                jsonUrl: PATH + 'schedules/get/' + type + '/' + method + '/' + category + query_id_str,
                eventList: false,
                setDate: d.getTime()/1000
            });
            
            $(document).find(".monthly-day[data-time='"+time+"']").addClass("active");

            $(document).on("click", ".monthly-day", function(){
                var that = $("#schedule-calendar");
                var time = $(this).data('time');
                var type = $(".sub-sidebar").find('[name="schedule_type"]:checked').val();
                var category = $(".sub-sidebar").find("input[name='schedule_of']:checked").val();
                var method = $(".sub-sidebar").find("[name='method_post']").val();
                var query_id = $(".sub-sidebar").find('[name="query_id"]').val();
                query_id = parseInt(query_id);
                var query_id_str = "";
                if(Number.isInteger(query_id)){
                    query_id_str = "?query_id=" + query_id;
                }

                var params = { token: csrf };
                var action = PATH + "schedules/index/" + type + "/" + method + "/" + category + "/" + time + query_id_str;

                $(document).find(".monthly-day").removeClass("active");
                $(this).addClass("active");
                Core.ajax_post( that, action, params, function(result){
                    $(".schedules-main").addClass("active");
                    $(".schedule-list").html(result);
                    Core.overplay("hide");
                    history.pushState(null, '', action);
                    $(".sub-sidebar").find('[name="schedule_time"]').val(time);
                    Layout.carousel();
                });
            });

            $(document).on("click", ".open-schedule-calendar", function(){
                $(".schedules-main").removeClass("active");
            });

           $(".sub-sidebar").find(".schedule-type").on("click", function(){
                var query_id = $(".sub-sidebar").find('[name="query_id"]').val();
                query_id = parseInt(query_id);
                var query_id_str = "";
                if(Number.isInteger(query_id)){
                    query_id_str = "?query_id=" + query_id;
                }

                var time = $(".sub-sidebar").find('[name="schedule_time"]').val();
                var url = $(this).attr("href") + "/" + time + query_id_str;
                location.assign( url );
                return false;
            });

            $(".sub-sidebar").find("input[name='schedule_of']").on("change", function(){
                var type = $(".sub-sidebar").find('[name="schedule_type"]:checked').val();
                var time = $(".sub-sidebar").find('[name="schedule_time"]').val();
                var method = $(".sub-sidebar").find("[name='method_post']").val();
                var query_id = $(".sub-sidebar").find('[name="query_id"]').val();
                query_id = parseInt(query_id);
                var query_id_str = "";
                if(Number.isInteger(query_id)){
                    query_id_str = "?query_id=" + query_id;
                }

                var category = $(this).val();
                var action = PATH + "schedules/index/" + type + "/" + method + "/" + category + "/" + time + query_id_str;
                location.assign( action );
                Core.overplay();
            });

            $(".sub-sidebar").find("[name='method_post']").on("change", function(){
                console.log(333);
                var type = $(".sub-sidebar").find('[name="schedule_type"]:checked').val();
                var time = $(".sub-sidebar").find('[name="schedule_time"]').val();
                var category = $(".sub-sidebar").find("[name='schedule_of']:checked").val();
                var query_id = $(".sub-sidebar").find('[name="query_id"]').val();
                query_id = parseInt(query_id);
                var query_id_str = "";
                if(Number.isInteger(query_id)){
                    query_id_str = "?query_id=" + query_id;
                }
                var method = $(this).val();
                var action = PATH + "schedules/index/" + type + "/" + method + "/" + category + "/" + time + query_id_str;

                location.assign( action );
                Core.overplay();
            });
        }
    }
}

var Schedules = new Schedules();
$(function(){
    Schedules.init();
});
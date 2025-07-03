"use strict";
function Instagram_profiles(){
    var self = this;
    this.init = function(){};

    this.Checkpoint = function(data){
        console.log(data);
        if(data.type == "challenge"){
            $(".ig_unofficial_login_form").addClass("d-none");
            $(".ig_unofficial_confirm_form").removeClass("d-none");
            $("#ig_type_1").val(data.type);
            $("#ig_method").val(data.method);
            $("#ig_api_path").val(data.api_path);
        }

        if(data.type == "two_factor_auth"){
            $(".ig_unofficial_login_form").addClass("d-none");
            $(".ig_unofficial_two_factor_confirm_form").removeClass("d-none");
            $("#ig_type_2").val(data.type);
            $("#ig_method").val(data.method);
            $("#ig_identifier").val(data.identifier);
        }
    };

    this.LoginPass = function(data){
        if(data.type == "login_pass"){
            $(".ig_unofficial_login_form").removeClass("d-none");
            $(".ig_unofficial_confirm_form").addClass("d-none");
        }
    };
}

var Instagram_profiles = new Instagram_profiles();
$(function(){
    Instagram_profiles.init();
});
var sidebarSliderCounter = 0;
var postidtolike = -1; //default
// TODO : Add Bitmoji to this blog page

// DEFINE Alertify definitions
alertify.set('notifier','position', 'bottom-center'); //set position    
alertify.set('notifier','delay', 1.5); //set dellay
alertify.minimalDialog || alertify.dialog('minimalDialog',function(){
    return {
        main:function(content){
            this.setContent(content); 
        }
    };
});


function slideSidebar() {
    var sidebar = document.getElementById("sidebar");
    var content = document.getElementById("container");
    if(sidebarSliderCounter % 2 == 0) {
        sidebar.className = "sidebar sidebar-open-animation";
        content.className = "container container-with-sidebar";
    }
    else {
        sidebar.className = "sidebar sidebar-close-animation";
        content.className = "container container-without-sidebar";
    }
    sidebarSliderCounter++;
}

function addComments() {
    document.getElementById("add-comment-div").style.display = "block";
    document.getElementById("manage-comment-div").style.display = "none";
    document.getElementById("manage-users-div").style.display = "none";
}

function manageComments() {
    document.getElementById("add-comment-div").style.display = "none";
    document.getElementById("manage-comment-div").style.display = "block";
    document.getElementById("manage-users-div").style.display = "none";
}

function manageUsers() {
    document.getElementById("add-comment-div").style.display = "none";
    document.getElementById("manage-comment-div").style.display = "none";
    document.getElementById("manage-users-div").style.display = "block";
    $.ajax({
        type: "POST",
        url: "main.php",
        data: {
            message: "display-users"
        },
        success: function(data) {
            $("#users-to-manage").html(data);
        }
    });
}

function deactiavte(id) {
    $.ajax({
        type: "POST",
        url: "main.php",
        data: {
            message: "deactivate-comment",
            commentid: id
        },
        success: function(data) {
            $("#comments-to-manage").html(data);
            alertify.success("Comment Deactivated!");
        }
    });
}

function activate(id){
    $.ajax({
        type: "POST",
        url: "main.php",
        data: {
            message: "activate-comment",
            commentid: id
        },
        success: function(data) {
            $("#comments-to-manage").html(data);
            alertify.success("Comment Activated!");
        }
    });
}

function allow_edit(id) {
    var form = document.getElementById("edit-comment-form");
    var text = "";
    form.style.display = "block";
    //first get the text as the value
    $.ajax({
        type: "POST",
        url: "main.php",
        data: {
            message: "comment-text-byid",
            commentid: id
        },
        success: function(data) {
            text = data;
            $("#edit-comment-form-textarea").val(text);
            alertify.confirm(
                form, 
                function() {
                    $.ajax({
                        type: "POST",
                        url: "main.php",
                        data: {
                            message: "edit-comment",
                            commentid: id,
                            comment: $("#edit-comment-form-textarea").val()
                        },
                        success: function(data) {
                            if($.trim(data) == "error") alertify.error("Error...");
                            else {
                                $("#manage-commet-text-"+id).text(data);
                                alertify.success("Comment Updated");
                            }    
                        }
                    });
                },
                function() {
                    alertify.message("Not Changed");
                }
            );
            form.style.display = "none";
        }
    });
    
}

function hi() { //for debugging purposes
    console.log("helloworld and hi");
}

function showLikes(id) {
    //populate the right div
    $.ajax({
        type: "POST",
        url: "main.php",
        data: {
            message: "populate-post-likes",
            postid: id
        },
        success: function (data) {
            if($.trim(data) == "false") alertify.error("Error...");
            else {
                $("#user-likes-"+id).html(data);
                //collapse the right div
                var content = document.getElementById("user-likes-"+id);
                if(content.style.maxHeight) content.style.maxHeight = null;
                else content.style.maxHeight = content.scrollHeight + "px";
            }
        }
    });   
}

function deleteUser(id) {
    $.ajax({
        type: "POST",
        url: "main.php",
        data: {
            message: "delete-user",
            userid: id,
            first: $("#user-manage-info-firstname-"+id).val(),
            last: $("#user-manage-info-lastname-"+id).val()
        },
        success: function(data) {
            if($.trim(data) == "true") alertify.success("User Deleted...");
            else if ($.trim(data) == "false") alertify.error("Error Deleting...");
        }
    });
}

function likePost(id) {
    postidtolike = id;
    $.ajax({
        type: "POST",
        url: "main.php",
        data: {
            message: "check-login-cookie",
            postid: id
        },
        success: function(data) {
            console.log("likePost: " + data);
            if($.trim(data) == "notfound") {
                var optionsdiv = document.getElementById("register-form-options");
                optionsdiv.style.display = "block";
                alertify.minimalDialog(optionsdiv).set('resizable',true).resizeTo('80%','60%');
            }
            else {
                $.ajax({
                    type: "POST",
                    url: "main.php",
                    data: {
                        message: "update-likes-amount",
                        postid: id
                    },
                    success: function(data) {
                        $("#likes-label-" + id).html(data);
                        $("#like-icon-"+id).toggleClass("fa-heart-o");
                        $("#like-icon-"+id).toggleClass("fa-heart");
                    }
                }); 
            }
                // alertify.success("Welcome " + data + "!");
        }
    });
}

function cleanNewCommentInputs() {
    var options = document.getElementsByName("rating-option");
    document.getElementById("comment-text").value = "";
    for(var i = 0; i < options.length; i++) {
        options[i].checked = false;
    }
    document.getElementById("attachment").value = "";
}

function signin(option, title) {
    var loginOption = "";
    var optionsdiv = document.getElementById("register-form-options");
    optionsdiv.style.display = "none";
    alertify.minimalDialog.close();
    var form = document.getElementById("registration-form-div");
    if(option == "cancel") {
        form.style.display = "none";
        alertify.message("Maybe Later...");
        return;
    }
    else if (option == "login") {
        $("#signin-instructions").text(title);
        form.style.display = "block";
        loginOption = "login";
    }
    else if(option == "signup") {
        $("#signin-instructions").text(title);
        form.style.display = "block";
        loginOption = "signup";
    }
    alertify.confirm( 
        form,
        function() {
            if($("#register-first-name").val().length < 2) signin(loginOption, 'Enter your first name');
            else if($("#register-last-name").val().length < 2) signin(loginOption, 'Enter your last name');
            else { //first and last name inputs are okay
                //next: check for taken pin if signing up
                if(loginOption = "signup") {
                    $.ajax({
                        type: "POST",
                        url: "main.php",
                        data: {
                            message: "login-user",
                            firstname: $("#register-first-name").val(),
                            lastname: $("#register-last-name").val(),
                            pin: $("#register-pin").val(),
                            post: postidtolike
                        },
                        success: function(data) {
                            if($.trim(data) != "notfound") {
                                alertify.confirm.close();
                                alertify.success("Welcome back, \r\n " + $.trim(data));
                            }
                            else if($.trim(data) == "notfound") {
                                alertify.message("Error...");
                                alertify.confirm.close();
                                signin("login", 'Your credentials are wrong...');
                            }
                        }
                    });
                }
                else if(loginOption = "login") {
                    $.ajax({
                        type: "POST",
                        url: "main.php",
                        data: {
                            message: "set-account",
                            firstname: $("#register-first-name").val(),
                            lastname: $("#register-last-name").val(),
                            pin: $("#register-pin").val(),
                            post: postidtolike
                        },
                        success: function(data) {
                            if($.trim(data) == "true") {
                                likePost(id);
                                alertify.success("Welcome, " + $("#register-first-name").val());
                            }
                            else if($.trim(data) == "false") {
                                alertify.message ("PIN taken").ondismiss = function() {
                                    $("#register-pin").val() = "";
                                    signin('signup', 'Choose a different PIN'); 
                                }
                            }
                        }
                    });
                }
            }
        },
        function() { //cancel
            alertify.confirm.close(); 
            alertify.message("Maybe Later...");
        }
    );
}   

function promptPassword(oldpw, title) {
    if(oldpw == "cookiefound") alertify.success("Welcome!"); //WELCOME
    else {
        alertify.prompt (
            title, 
            "To post a blog message you need the Shahafster's Passowrd", 
            "",
            function(event, value){
                if(value == oldpw)  {
                    // add cookie for loging in
                    $.ajax({
                        type: "POST",
                        url: "main.php",
                        data: {
                            message: "login-cookie-start"
                        },
                        success: function(data) {
                            if($.trim(data) == "true") alertify.success("Welcome!"); //WELCOME
                        }
                    }); 
                }
                else {
                    alertify.error("Wrong Password!").ondismiss = function() {
                        promptPassword(oldpw, "Wrong Password...");
                    }
                }
            },
            function() {
                alertify.message("Goodbye...").ondismiss = function() {
                    location.replace("index.html");
                }
            }
        );
    }
}

function displayCurrentUser() {
    $.ajax({
        type: "POST",
        url: "main.php",
        data: {
            message: "get-current-cookie-user"
        },
        success: function(data) {
            if($.trim(data) == "nouser") return "nouser";
            else {
                var names = $.trim(data).split('\\s+');
                alertify.success("Welcome back, "+names[0]);
                return $.trim(data);
            }
        }
    });
}
  
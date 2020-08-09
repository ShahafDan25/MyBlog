var sidebarSliderCounter = 0;
// TODO : Add Bitmoji to this blog page

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
    $.ajax({
        type: "POST",
        url: "main.php",
        data: {
            message: "check-login-cookie",
            postid: id
        },
        success: function(data) {
            if($.trim(data) == "notfound") {
                document.getElementById("register-form").style.display = "block";
                //create account
                register(id);
            }
            else {
                // alertify.success("Welcome " + data + "!");
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

function register(id) {
    var form = document.getElementById("register-form");
    alertify.confirm(
        form,
        function() { //register
            $.ajax({
                type: "POST",
                url: "main.php",
                data: {
                    message: "set-account",
                    firstname: $("#register-first-name").val(),
                    lastname: $("#register-last-name").val(),
                    pin: $("#register-pin").val(),
                    post: id
                },
                success: function(data) {
                    if($.trim(data) == "true") {
                        // $("#like-icon-"+id).toggleClass("fa-heart-o");
                        // $("#like-icon-"+id).toggleClass("fa-heart");
                        likePost(id);
                        alertify.success("Welcome, " + $("#register-first-name").val());
                    }
                    else if($.trim(data) == "false") {
                        alertify.message ("PIN taken").ondismiss = function() {
                            $("#register-pin").val() = "";
                            register(id); 
                        }
                    }
                }
            }); 
        },
        function () { //cancel
            alertify.message("Maybe Later...");
            document.getElementById("register-form").style.display = "none";
        }
    ).set({labels:{ok: 'Submit', cancel: 'Cancel'}, padding: false}); 
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
  
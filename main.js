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
}

function manageComments() {
    document.getElementById("add-comment-div").style.display = "none";
    document.getElementById("manage-comment-div").style.display = "block";
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
            //then present the replacement text form
            alertify.prompt( // DOUBLE AJAX WOOHOO
                "Edit Comment  #" + id,
                "Edit the comment below...",
                text,
                function(event, value) {
                    $.ajax({
                        type: "POST",
                        url: "main.php",
                        data: {
                            message: "edit-comment",
                            commentid: id,
                            comment: value
                        },
                        success: function(data) {
                            $("#comments-to-manage").html(data);
                            alertify.success("Comment Updated");
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

  
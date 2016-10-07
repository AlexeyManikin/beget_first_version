function savePasswd(){
    var old_passwd  = document.getElementById("old_passwd").value;
    var new_passwd  = document.getElementById("new_passwd").value;
    var new_passwd2 = document.getElementById("new2_passwd").value;
    if (new_passwd != new_passwd2){
        alert("¬веденные пароли не совпадают");
    } else if (new_passwd.length == 0){
        alert("ѕароль не может быть пустым");
    } else
    ajax_save_passwd(old_passwd,new_passwd,
    function(req){
        var result = req.responseText;
        if (result == '1'){
            alert("¬веден неправильный пароль");
        } else if (result == '0'){
            alert("ѕароль успешно сменен");
            location.href='..';
        }
    }
    );
}
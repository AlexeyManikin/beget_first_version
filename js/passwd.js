function savePasswd(){
    var old_passwd  = document.getElementById("old_passwd").value;
    var new_passwd  = document.getElementById("new_passwd").value;
    var new_passwd2 = document.getElementById("new2_passwd").value;
    if (new_passwd != new_passwd2){
        alert("��������� ������ �� ���������");
    } else if (new_passwd.length == 0){
        alert("������ �� ����� ���� ������");
    } else
    ajax_save_passwd(old_passwd,new_passwd,
    function(req){
        var result = req.responseText;
        if (result == '1'){
            alert("������ ������������ ������");
        } else if (result == '0'){
            alert("������ ������� ������");
            location.href='..';
        }
    }
    );
}
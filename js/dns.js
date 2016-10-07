function update_domain(){
    var new_ddomain = document.getElementById("domain").value;
    ajax_set_domain(new_ddomain,
    function(req){
        var result = req.responseText;
        if (result == 'Changet'){
            domain_reload();  
        } else {
            alert("Данный домен не удален с текущего аккаунта, пожалуйста обновите страницу");
        }
    });
};

function domain_reload(){
    ajax_get_domain_type(
        function(req){
            var domain_type = req.responseText;
            setDomainType(domain_type);
        }
    );
    setType();
    setRecordType();
    setDate();
}

function setDomainType(type){
    var v1 = document.getElementById("type_a");
    var v2 = document.getElementById("subdomain_edit");
    v1.click();
    if (type == 'domain'){
        v2.style.display   = 'none';
    } else if (type == 'subdomain'){
        v2.style.display   = 'block';
    } else {
        alert("Неправильный параметр");
    }
}

function setDate(){
    ajax_get_rra(
        function(req){
            var rr   = document.getElementById("rr_a");
            rr.value = req.responseText;
        }
    );
    ajax_get_mx1(
        function(req){
            var rr   = document.getElementById("rr_mx1");
            rr.value = req.responseText;
        }
    );
    ajax_get_mx2(
        function(req){
            var rr   = document.getElementById("rr_mx2");
            rr.value = req.responseText;
        }
    );
    ajax_get_ns1(
        function(req){
            var rr   = document.getElementById("rr_ns1");
            rr.value = req.responseText;
        }
    );
    ajax_get_ns2(
        function(req){
            var rr   = document.getElementById("rr_ns2");
            rr.value = req.responseText;
        }
    );
    ajax_get_cmane(
        function(req){
            var rr   = document.getElementById("rr_cname");
            rr.value = req.responseText;
        }
    );
}

function setType(){
    ajax_get_type(
        function(req){
            var type = req.responseText;
            var i = parseInt(type);
            if (i == 0)
                document.typeRec.opt_type[0].click();
            if (i == 1)
                document.typeRec.opt_type[1].click();
            if (i == 2)
                document.typeRec.opt_type[2].click();
        }
    );
}

function setRecordType(){
    var r1 = document.getElementById("record_a");
    var r2 = document.getElementById("record_ns");
    var r3 = document.getElementById("record_cname");
    r1.style.display   = 'none';
    r2.style.display   = 'none';
    r3.style.display   = 'none';
    var s1 = document.getElementById("type_a");
    var s2 = document.getElementById("type_ns");
    var s3 = document.getElementById("type_cname");
    if (s1.checked){
        r1.style.display   = 'block';
    }
    if (s2.checked){
        r2.style.display   = 'block';
    }
    if (s3.checked){
        r3.style.display   = 'block';
    }
}

function send_date(){
    var domain   = document.getElementById("domain").value;
    var rr_a     = document.getElementById("rr_a").value;
    var rr_mx1   = document.getElementById("rr_mx1").value;
    var rr_mx2   = document.getElementById("rr_mx2").value;
    var rr_ns1   = document.getElementById("rr_ns1").value;
    var rr_ns2   = document.getElementById("rr_ns2").value;
    var rr_cname = document.getElementById("rr_cname").value;
    var type;
    if (document.typeRec.opt_type[0].checked)
        type = '0';
    if (document.typeRec.opt_type[1].checked)
        type = '1';
    if (document.typeRec.opt_type[2].checked)
        type = '2';
    ajax_seve(domain,type,rr_a,rr_mx1,rr_mx2,rr_ns1,rr_ns2,rr_cname,
        function(req){
            var result = req.responseText;
            if (result == '0')
                alert("Параметры домена успешно сохранены");
            else
                alert("Проверте коррекстность введенный параметров код ошибки "+result);
        } 
    );
}
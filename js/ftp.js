function error_message(id)
{
    switch(id)
    {
    case '0':
        break;
    case '1':
        alert('������ ��� ��������� � ���������� �������, ���������� � ������ ��������� support@beget.ru');
        break;
    case '10':
        alert('����� ������ �� ����� ���� ����� ������ �������');
        break;
    case '11':
        alert('����� ������ �� ����� ��������� 10 ��������');
        break;
    case '12':
        alert('����� ����� �������� ������ �� ���� � ���� ���������� ��������, � ����� ����� �������������');
        break;
    case '20':
        alert('����� ������ �� ����� ���� ����� ������ �������');
        break;
    case '21':
        alert('����� ������ �� ����� ��������� 10 ��������');
        break;
    case '22':
        alert('������ ����� �������� ������ �� ���� � ���� ���������� ��������, � ����� ����� �������������');
        break;    
    case '2':
        alert('�� ��������� ����������� ���������� ���������� FTP ��������� ��� ������ ��������� �����');
        break;
    default:
        alert('������ ������� (code:' + id + '), ���������� � ������ ��������� support@beget.ru');
    }
}

function delete_ftp(account)
{
    if (!confirm("�� ������������� ������ ������� FTP ������ " + account + "?")) return;
    ajax_delete_ftp(account,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              setTimeout("reload_table('ftp_table');", 5);
              alert('FTP ������� ������� ������');
           } else
           {
              error_message(return_value);
           }
           
        });
};

function change_password(account)
{
    var text       = "������� ������ ��� FTP �������� " + account;
    var new_passwd = window.prompt(text,"");
    if (new_passwd != null)
    {
        ajax_change_ftp_passwd(account,new_passwd,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              setTimeout("reload_table('ftp_table');", 5);
              alert('������ ������� �������');
           } else
           {
              error_message(return_value);
           }
           
        });
    };   
};

function create_ftp_user(path)
{
    var login      = document.getElementById("login_" + path).value;
    if (login.length == 0)
    {
        alert('����� ������ �� ����� ���� �������');
        return false;
    };
    
    var user_path  = path + "/" + document.getElementById("path_" + path).value;
    var text       = "������� ������ ��� FTP �������� " + login;
    var new_passwd = window.prompt(text,"");
    if (new_passwd != null)
    {
        ajax_craete_ftp_user(login,user_path,new_passwd,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              setTimeout("reload_table('ftp_table');", 5);
              alert('FTP ������� ������� ������');
           } else
           {
              error_message(return_value);
           }
           
        });
    };  
};

function create_ftp_user2()
{
    var login      = document.getElementById("login").value;
    if (login.length == 0)
    {
        alert('����� ������ �� ����� ���� �������');
        return false;
    };
    var user_path  = document.getElementById("path").value;
    var new_passwd = document.getElementById("passwd").value;
    if (new_passwd != null)
    {
        ajax_craete_ftp_user(login,user_path,new_passwd,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              setTimeout("reload_table('ftp_table');", 5);
              alert('FTP ������� ������� ������');
           } else
           {
              alert('������ ��� �������� FTP �������� (code:' + return_value + '), ���������� � ������ ��������� support@beget.ru');
           }
           
        });
    };   
};

var tables = new Array();

var table_fields = [ {name: 'domain', size: '250',   caption: '����'},
                     {name: 'login',  size: '110',   caption: 'FTP �����'},
                     {name: 'path',   size: '220',   caption: '����'},];

// �������� ���� �� �����
function get_field_hash(f_name){
  for(var i in table_fields)
      if (f_name == table_fields[i].name)
         return table_fields[i]
  return 0;
};

// ����������� ����� ��������
function count_cell_width(t_width, fields){

  var counters = new Array();
  for(var i in fields){
    var field = get_field_hash(fields[i]);
    if (field.size > 0) counters[i] = field.size; 
    else counters[i] = 100;
  };
  return counters;
};

function create_tag(tagname, id, options, style, inner){
  return "<" + tagname + ((id == "")?"":" id=\""+id+"\"") + ((options == "")?"":" "+options+"") + ((style == "")?"":" style=\""+style+"\"") + ">"+inner + "</" +tagname+">";
};

function create_s_tag(tagname, id, options, style){
  return "<" + tagname + ((id == "")?"":" id=\""+id+"\"") + ((options == "")?"":" "+options+"") + ((style == "")?"":" style=\""+style+"\"") + "/>";
};

// �������� ���� �� �����
function get_field_hash(f_name){
  for(var i in table_fields)
      if (f_name == table_fields[i].name)
          return table_fields[i];
  return 0;
};

// �������� ������� �� ������
function get_table(name){
  for (var i in tables)  
      if (tables[i].name == name)  
          return tables[i];
};

// ��������� �������
function save_table(name, table){
  for (var i in tables)
    if (tables[i].ft_name == name){
      tables[i] = table;
      return;
  };
};

function init_table(name, t_width, fields, methods){
  var table = {  name:      name, 
                 width:     t_width,
                 fields:    fields,
                 methods:   methods
               };
  tables.push(table);
  document.write(create_tag("div", "div_ft_" + name, "", "", ""));
};


function load_list(nametable,callback){
  ajax_load_list(
    function(req){
      var returnArray = String(req.responseText).split("\n");
      var list = new Array();
      for(var i in returnArray){
        var element = returnArray[i];
        var a_list = String(element).split("|");
        var type   = a_list[0]
        var path   = a_list[1];
        var data   = a_list[2];
        if (type.length > 0){
            list.push({type: type, path: path, data: data});
        }
      };
      callback(nametable, list);
    }
  );
}


function reload_table(name)
{
  var table = get_table(name);
  var c_width = count_cell_width(table.width, table.fields);

  var h_fields = "";
  for(var i in table.fields){
    var field = get_field_hash(table.fields[i]);
    var width = c_width[i];
    h_fields = h_fields + create_tag("td", "", "class=\"tableheader\" width=\""+width+"\"", "", 
      create_tag("center", "", "", "", field.caption));
  };

  table.methods.get_list(name,
    function(name,list){
      var table = get_table(name);
      var files_tag = "";

      if (list.length == 0); else 
      for(f_i in list){
        var c_returned = list[f_i];

        var cells_tag = "";
        for(var i in table.fields){
          var field = get_field_hash(table.fields[i]);

          var caption = "";
          switch(field.name){
            case 'domain':
                if (c_returned.type == 's'){
                    if (c_returned.data > 0){
                        caption = create_tag("div", "", "", "color:red;","&nbsp;&nbsp;&nbsp;/" + c_returned.path );
                    } else
                    {
                        caption = create_tag("div", "", "", "","&nbsp;&nbsp;&nbsp;/" + c_returned.path );
                    }
                } else {
                    caption = create_tag("center", "", "", "", 
                        create_tag("a", "", "href=\"#\" onclick=\"javascript:delete_ftp('" + c_returned.data + "');return false;\"", "", create_s_tag(
                        "img", "", "src=\"images/del.png\" border = \"0\" alt=\"�������\" title=\"�������\"", ""))
                        + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
                        + create_tag("a", "", "href=\"#\" onclick=\"javascript:change_password('" + c_returned.data + "');return false;\"", "", create_s_tag(
                        "img", "", "src=\"images/edit.png\" border = \"0\" alt=\"�������� ������\" title=\"�������� ������\"", ""))
                        );
                }
                break;
            case 'login':
                if (c_returned.type != 's'){
                    caption = create_tag("div", "", "", "", "&nbsp;&nbsp;&nbsp;" + c_returned.data);    
                } else
                {
                    caption = create_tag("center", "", "", "",
                    create_s_tag(
                        "input", "login_" + c_returned.path, "type=\"text\"", "width:100px;")
                    ); 
                }
                break;
            case 'path':
                if (c_returned.type != 's'){
                    caption = create_tag("div", "", "", "", "&nbsp;&nbsp;&nbsp;" + c_returned.path);    
                } else
                {
                    caption = create_tag("center", "", "", "",
                    create_s_tag(
                        "input", "path_" + c_returned.path, "type=\"text\"", "width:160px;")
                    + "&nbsp;&nbsp;&nbsp;"
                    + create_tag("a", "", "href=\"#\" onclick=\"javascript:create_ftp_user('" + c_returned.path + "');return false;\"", "",
                    create_s_tag("img", "", "src=\"images/add.png\" border=\"0\" alt=\"�������\" title=\"�������\"", ""))
                    ); 
                }
                break;
           };
          cells_tag = cells_tag + create_tag("td", "", "align=\"left\"", "height:23;", caption);
        };
        if (c_returned.type == 's')
            files_tag = files_tag + create_tag("tr", "mail_table_"+c_returned.type + c_returned.path, "class=\"tabledata1\"", "", cells_tag);
        else
            files_tag = files_tag + create_tag("tr", "mail_table_"+c_returned.type + c_returned.path, "class=\"tabledata2\"", "", cells_tag);
    };

    var file_table = create_tag("table", "", "border=0 cellpadding=1 cellspacing=1", "", 
      create_tag("tr", "", "", "", h_fields) + files_tag
    );

    document.getElementById("div_ft_" + table.name).innerHTML = file_table;
    save_table(name, table);
    }
  );
}
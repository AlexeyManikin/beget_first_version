// �� �������� ���������� ������
function error_message(id)
{
    switch(id)
    {
    case '0':
        break;
    case '1':
        alert('������ ��� ��������� � ���������� �������, ���������� � ������ ��������� support@beget.ru.');
        break;
    case '2':
        alert('���� ������ � ����� ������� ��� ������������ � �������.');
        break;
    case '3':
        alert('�������� �����, ���������� � ������ ��������� support@beget.ru.');
        break;
    case '4':
        alert('���� ������ �� ����������, ���������� � ������ ��������� support@beget.ru.');
        break;
    case '5':
        alert('������ ����������� ����� �������.');
        break;
    case '10':
        alert('����� ������ �� ����� ���� ����� ������ �������.');
        break;
    case '11':
        alert('����� ������ �� ����� ��������� 10 ��������.');
        break;
    case '12':
        alert('����� ����� �������� ������ �� ���� � ���� ���������� ��������, � ����� ����� �������������.');
        break;
    case '20':
        alert('����� ������ �� ����� ���� ����� ������ �������.');
        break;
    case '21':
        alert('����� ������ �� ����� ��������� 10 ��������.');
        break;
    case '22':
        alert('������ ����� �������� ������ �� ���� � ���� ���������� ��������, � ����� ����� �������������.');
        break;    
    default:
        alert('������ ������� (code:' + id + '), ���������� � ������ ��������� support@beget.ru.');
    }
}

function set_count_db()
{
    ajax_get_count_db(function(req){
           var return_value = String(req.responseText);
           var text  = document.getElementById("count_mysql");
           text.innerHTML = return_value;
        });
}

function add_database()
{
    var login  = document.getElementById("login").value;
    var passwd = document.getElementById("passwd").value;
    
    box = new String(login);
    if (box.length < 2)
    {
        alert('����� ������ ������ ���� �� ����� 2 ��������.');
        return false;
    }
    
    if (box.length > 8)
    {
        alert('����� ������ ������ ���� �� ����� 8 ��������.');
        return false;
    }
    
    if (!chack_login(login))
    {
        alert('����� �������� ������������ �������.');
        return false;
    }
    
    ajax_create_db(login,passwd,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              setTimeout("reload_table('mysql_table');", 5);
              set_count_db();
              alert('���� ������ ������� �������.');
           } else
           {
              error_message(return_value);
           }
           
        });  
};

function drop_db(dbname)
{
    if (!confirm("�� ������������� ������ MySQL  " + dbname + "?")) return;
    ajax_drop_db(dbname,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              setTimeout("reload_table('mysql_table');", 5);
              set_count_db();
              alert('���� ������ ������� �������.');
           } else
           {
              error_message(return_value);
           }
           
        });  
};

function add_access(dbname)
{
    var dest = document.getElementById("path_"+dbname).value;
    string = new String(dest);
    if (string.length < 1)
    {
        alert('�������, ����������, IP ����� ��� �������� ��� ��� ����������� � ��.');
        return;
    }
    
    if (!check_domain(dest))
    {
        alert('�������, ����������, ���������� �������� ��� ��� IP �����.');
        return;
    }
    
    var text = "������� ������ ��� ������� � �� " + dbname + " c " + dest;
    var new_passwd = window.prompt(text,"");
    if (new_passwd != null)
    {
        ajax_create_access(dbname,dest,new_passwd,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              setTimeout("reload_table('mysql_table');", 5);
              alert('������ ������� ������.');
           } else
           {
              error_message(return_value);
           }
           
        });  
    }
};

function drop_access(dbname,access)
{
    if (!confirm("�� ������������� ������ ������� ������ � �� " + dbname + " � ������ " + access + "?")) return;
    ajax_drop_access(dbname,access,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              setTimeout("reload_table('mysql_table');", 10);
              alert('������ ������� ������.');
           } else
           {
              error_message(return_value);
           }
           
        });  
};

function changePasswd(dbname,access)
{
    var text       = "������� ������ ��� ������� � �� "+dbname+ " c "+access;
    var new_passwd = window.prompt(text,"");
    if (new_passwd != null)
    {
    	ajax_change_passwd(dbname,access,new_passwd,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              alert('������ ������� �������.');
           } else
           {
              error_message(return_value);
           }
        });  	
    }
};

function open_phpMyAdmin(dbname)
{
    var form       = document.getElementById("phpMyAdminForm");
    var text       = "������� ������ ��� ������� � �� " + dbname;
    var new_passwd = window.prompt(text,"");
    if (new_passwd != null)
    {
    	form.pma_username.value = dbname;
    	form.pma_password.value = new_passwd;
    	form.submit();	
    }
}

var tables = new Array();

var table_fields = [ {name: 'login', size: '140',   caption: '�����'},
                     {name: 'size',  size: '70',    caption: '������'},
                     {name: 'access',size: '185',   caption: '������'},
                     {name: 'do',    size: '185',   caption: '��������'}];

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
        if (String(element).length > 0){
          var a_list = String(element).split("|");
          var type   = a_list[0]
          var login  = a_list[1];
          var data   = a_list[2];
          if (type.length > 0){
              list.push({type: type, login: login, data: data});
          };
        };
      };
      callback(nametable, list);
    }
  );
};


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
            case 'login':
                if (c_returned.type == 'b'){
                    caption = create_tag("strong", "", "", "", "&nbsp;&nbsp;&nbsp;" + c_returned.login);    
                } 
                break;
            case 'size':
                if (c_returned.type == 'b'){
                    caption = create_tag("center", "", "", "",
                              create_tag("div", "", "", "", "&nbsp;&nbsp;&nbsp;" + c_returned.data + " ��."));    
                } 
                break;
            case 'access':
                if (c_returned.type == 'a'){
                    caption = create_tag("div", "", "", "", "&nbsp;&nbsp;&nbsp;" + c_returned.login);    
                } else
                {
                    caption = create_tag("center", "", "", "",
                    create_s_tag(
                        "input", "path_" + c_returned.login, "type=\"text\"", "width:180px; valign:center")
                   ); 
                }
                break;
             case 'do':
                if (c_returned.type == 'b'){
                    caption = create_tag("center", "", "", "", create_tag("a", "", "href=\"#\" onclick=\"javascript:add_access('" + c_returned.login + "');return false;\"", "",
                    create_s_tag("img", "", "src=\"images/add.png\" border=\"0\" alt=\"�������� ������\" title=\"�������� ������\"", ""))
                        + "&nbsp;&nbsp;&nbsp;"
                        + create_tag("a", "", "href=\"#\" onclick=\"javascript:drop_db('" + c_returned.login + "');return false;\"", "", create_s_tag(
                        "img", "", "src=\"images/del.png\" border = \"0\" alt=\"������� "+ c_returned.login +"\" title=\"������� " + c_returned.login + "\"", ""))
                        + "&nbsp;&nbsp;&nbsp;"
                        + create_tag("a", "", "href=\"#\" onclick=\"javascript:open_phpMyAdmin('" + c_returned.login + "');return false;\"", "", create_s_tag(
                        "img", "", "src=\"images/phpmyadmin.png\" border = \"0\" width=\"70\" alt=\"������� "+ c_returned.login +" � PhpMyAdmin\" title=\"������� "+ c_returned.login +" � PhpMyAdmin\"", "")));
                } else
                {
                    caption = create_tag("center", "", "", "", create_tag("a", "", "href=\"#\" onclick=\"javascript:changePasswd('" + c_returned.data + "','" + c_returned.login + "');return false;\"", "",
                    create_s_tag("img", "", "src=\"images/edit.png\" border=\"0\" alt=\"�������� ������\" title=\"�������� ������\"", ""))
                        + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
                        + create_tag("a", "", "href=\"#\" onclick=\"javascript:drop_access('" + c_returned.data + "','" + c_returned.login + "');return false;\"", "", create_s_tag(
                        "img", "", "src=\"images/del.png\" border = \"0\" alt=\"������� ������\" title=\"������� ������\"", "")));
                }
                break;
            };
          cells_tag = cells_tag + create_tag("td", "", "align=\"left\" ", "", caption);
        };
        if (c_returned.type == 'b')
            files_tag = files_tag + create_tag("tr", "mysql_table_base_"+c_returned.login, "class=\"tabledata1\"", "", cells_tag);
        else
            files_tag = files_tag + create_tag("tr", "mysql_table_access_"+ c_returned.data + "_" + c_returned.login, "class=\"tabledata2\"", "", cells_tag);
    };

    var file_table = create_tag("table", "", "border=0 cellpadding=1 cellspacing=1", "", 
      create_tag("tr", "", "", "", h_fields) + files_tag
    );

    document.getElementById("div_ft_" + table.name).innerHTML = file_table;
    save_table(name, table);
    }
  );
}

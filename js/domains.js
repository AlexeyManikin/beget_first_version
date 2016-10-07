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
        alert('�������� ��� ����� �������� ������ �� ���� ���������� ��������, ���� � �������� ������������� � ����');
        break;
    case '3':
        alert('������� �������� ����, ���������� � ������ ��������� support@beget.ru.');
        break;
    case '4':
        alert('����� ��� ������������ � �������.');
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

function dropDomain(domain)
{
    if (!confirm("�� ������������� ������ ������� ����� " + domain + "?")) return;
    ajax_drop_domain(domain,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              setTimeout("reload_table('domains_table');", 5);
              alert('����� ������� ������.');
           } else
           {
              error_message(return_value);
           }
        });
}

function moveDomain()
{
    var domain = document.getElementById("domain").value;
    var tld    = document.getElementById("tld").value;

    domain = new String(domain);
    if (domain.length == 0)
    {
        alert('������� ���������� ������� ���');
        return;
    }

    if (!check_domain(domain))
    {
        alert('�������� ��� ����� �������� ������ �� ���� ���������� ��������, ���� � �������� ������������� � ����');
        return;
    }

    ajax_move_domain(domain,tld,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              setTimeout("reload_table('domains_table');", 5);
              alert('����� ������� ���������.');
           } else
           {
              error_message(return_value);
           }
        });
} 

var tables = new Array();

var table_fields = [ {name: 'domain', size: '200',   caption: '�����'},
                     {name: 'date',  size: '200',   caption: '���� ��������'},
                     {name: 'do',    size: '150',   caption: '��������'}];

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
          var name   = a_list[0]
          var date   = a_list[1];
          if (name.length > 0){
              list.push({name: name, date: date});
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
      var flag = '0';

      if (list.length == 0); else 
      for(f_i in list){
        var c_returned = list[f_i];
        
        if (flag == '1')
            flag = '0';
        else
            flag = '1';

        var cells_tag = "";
        for(var i in table.fields){
          var field = get_field_hash(table.fields[i]);

          var caption = "";
          switch(field.name){
            case 'domain':
                caption = create_tag("strong", "", "", "", "&nbsp;&nbsp;&nbsp; www." + c_returned.name);    
                break;
            case 'date':
                caption = create_tag("div", "", "", "", "&nbsp;&nbsp;&nbsp;" + c_returned.date);    
                break;
             case 'do':
                caption = create_tag("center", "", "", "", create_tag("a", "", "href=\"#\" onclick=\"javascript:dropDomain('" + c_returned.name + "');return false;\"", "",
                    create_s_tag("img", "", "src=\"images/del.png\" border=\"0\" alt=\"������� �����\" title=\"�������� �����\"", ""))
                );
                break;
            };
          cells_tag = cells_tag + create_tag("td", "", "align=\"left\" ", "", caption);
        };
        if (flag == '1')
            files_tag = files_tag + create_tag("tr", "domain_table_"+ c_returned.name, "class=\"tabledata1\"", "", cells_tag);
        else
            files_tag = files_tag + create_tag("tr", "domain_table_"+ c_returned.name, "class=\"tabledata2\"", "", cells_tag);
    };

    var file_table = create_tag("table", "", "border=0 cellpadding=1 cellspacing=1", "", 
      create_tag("tr", "", "", "", h_fields) + files_tag
    );

    document.getElementById("div_ft_" + table.name).innerHTML = file_table;
    save_table(name, table);
    }
  );
}

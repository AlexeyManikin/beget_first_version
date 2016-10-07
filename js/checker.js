function check_mail(mailbox)
{
    Box = new String(mailbox);
    if (Box.match(/^[a-zA-Z0-9\.\-\_]+\@[\w\-\.\_]+$/) == null)
    {
        return false;
    };
    return true;
}

function check_number(number)
{
    Box = new String(number);
    if (Box.match(/^[0-9]*$/) == null)
    {
        return false;
    };
    return true;
}

function check_domain(domain)
{
    Box = new String(domain);
    if (Box.match(/^[a-zA-Z0-9\-\_\.]+$/) == null)
    {
        return false;
    };
    return true;
}

function chack_login(login)
{
    Box = new String(login)
    if (Box.match(/^[a-zA-Z0-9\-\_]+$/) == null)
    {
        return false;
    };
    if (Box.length > 10)
    {
        return false;
    };
    if (Box.length < 2)
    {
        return false;  
    };
    return true;
}
function urlencode(value)
{
	return encodeURIComponent(value);
}

function urldecode(value)
{
	return decodeURIComponent(value);
}

function loadPage(location)
{
	window.location.href = location;
	return false;
}

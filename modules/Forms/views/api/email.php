<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<body>
  @foreach($data as $key => $value)
    <p><strong>{{ $key }}:</strong> {{ (is_string($value) ? $value : json_encode($value)) }}</p>
  @endforeach
</body>
</html>

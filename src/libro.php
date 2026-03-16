<?php 
$GROQ_API_KEY = "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";
$url = "";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

/*
    {
        "model": "llama-3.3-70b-versatile",
        "messages": [{
            "role": "user",
            "content": "Explain the importance of fast language models"
        }]
    }
*/

$request_array = array();

$request_array["model"] = "llama-3.3-70b-versatile";
$request_array["messages"] = array();

$json_string = json_encode($request_array);

curl_setopt($ch, CURLOPT_POSTFIELDS, $json_string);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "Authorization: Bearer QUI:API:KEY"
]);

$risp = curl_exec($ch);

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

print($http_code);
print("<br />");

$responde_array = json_decode($risp, true);

print($response_array(['value']));
?>


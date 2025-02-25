<div>
    <input type="file" id="upload-docx" />
    <div id="output"></div>
</div>
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script>
    <script>
        document.getElementById("upload-docx").addEventListener("change", function(event) {
            var reader = new FileReader();
            reader.onload = function(event) {
                var arrayBuffer = reader.result;
                mammoth.convertToHtml({
                        arrayBuffer: arrayBuffer
                    })
                    .then(displayResult)
                    .catch(handleError);
            };
            reader.readAsArrayBuffer(this.files[0]);
        });

        function displayResult(result) {
            document.getElementById("output").innerHTML = result.value;
        }

        function handleError(err) {
            console.log(err);
        }
    </script>
@endpush

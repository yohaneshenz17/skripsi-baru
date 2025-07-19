<script src="<?= base_url() ?>cdn/vendor/jquery/dist/jquery.min.js"></script>
<script src="<?= base_url() ?>cdn/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url() ?>cdn/vendor/js-cookie/js.cookie.js"></script>
<script src="<?= base_url() ?>cdn/vendor/jquery.scrollbar/jquery.scrollbar.min.js"></script>
<script src="<?= base_url() ?>cdn/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js"></script>
<script src="<?= base_url() ?>cdn/vendor/clipboard/dist/clipboard.min.js"></script>
<script src="<?= base_url() ?>cdn/js/argon.js?v=1.2.0"></script>
<script src="<?= base_url() ?>cdn/plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script>
    var base_url = '<?= base_url() ?>';

    // Select 2 
    $('.select2').each(function() {
        $(this).select2({
            theme: 'bootstrap4',
            allowClear: true,
        });
    });

    // Date Time
    $(".dateTime").flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });

    function call(url, data = null) {
        return $.ajax({
            url: base_url + url,
            method: 'POST',
            data: data
        }).fail(function(err) {
            notif('error : ' + err.statusText, 'warning', true);
        });
    }

    async function notif(message, type, mixin) {
        if (mixin) {
            const Toast = Swal.mixin({
                position: 'top-end',
                toast: true,
                showConfirmButton: false,
                showCloseButton: true,
                timer: 3000
            })
            await Toast.fire({
                title: message,
                icon: type
            })
        } else {
            await Swal.fire({
                title: type[0].toUpperCase() + type.slice(1),
                text: message,
                icon: type
            }).then(s => {
                setTimeout(() => {
                    document.body.style.paddingRight = '0';
                }, 400)
            });
        }
    }

    function read(selector, callback) {
        file = document.querySelector(selector).files[0];
        var reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function() {
            callback({
                file: file.name,
                result: reader.result
            });
        }
        reader.onerror = function(err) {
            notif('file tidak terbaca', 'warning', true);
        }
    }
</script>
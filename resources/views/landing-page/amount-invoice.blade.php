<form action="{{ url('/pembayaran-cash/' . $transaksi['id']) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="form-group">
            <label for="amount" class="mb-2">Harga Transaksi</label>
            <input type="text" class="form-control" id="amount"
                value="{{ number_format($transaksi['totalHarga'], 0, ',', '.') }}" disabled>
        </div>
        <div class="form-group">
            <label for="received_amount" class="mb-2">Uang Diterima</label>
            <input type="text" class="form-control" name="received_amount" id="received_amount" required>
        </div>
        <div class="form-group">
            <label for="change" class="mb-2">Kembalian</label>
            <input type="text" class="form-control" id="change" readonly>
        </div>
    </div>
    <div class="modal-footer">
        <button type="reset" class="btn btn-danger btn-xs" style="margin-right: 10px">
            <i class="fa fa-times"></i> Batal
        </button>
        <button type="submit" class="btn btn-primary btn-xs">
            <i class="fa fa-edit"></i> Simpan
        </button>
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#received_amount').on('input', function() {
            var totalHarga = parseCurrency($('#amount').val());
            var receivedAmount = parseCurrency($(this).val());
            var change = receivedAmount - totalHarga;

            if (!isNaN(change) && change >= 0) {
                $('#change').val(formatCurrency(change));
            } else {
                $('#change').val('');
            }
        });

        $('#received_amount').on('blur', function() {
            $(this).val(formatCurrency(parseCurrency($(this).val())));
        });

        function parseCurrency(value) {
            return parseFloat(value.replace(/[^0-9,-]+/g, '').replace(',', '.'));
        }

        function formatCurrency(value) {
            return 'Rp ' + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').replace('.00', '');
        }
    });
</script>

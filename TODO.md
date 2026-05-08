# TODO: Adjust TransactionModel and DompetModel to match migrations

- [x] Update TransactionModel.php: Change primaryKey to 'id', update fillable fields to match migration, add belongsTo relation to DompetModel
- [x] Update DompetModel.php: Fix transaksi relation to use TransactionModel::class and 'id_dompet'

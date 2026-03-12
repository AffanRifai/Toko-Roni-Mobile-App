@extends('layouts.app')

@section('content')
<h1>Laporan Penjualan</h1>

<form method="GET">
    <label>Tanggal:</label>
    <input type="date" name="date">

    <label>Bulan:</label>
    <input type="month" name="month">

    <button>Tampilkan</button>
</form>

<hr>

<h3>Total Omzet: Rp {{ number_format($total) }}</h3>

<table>
<tr>
    <th>Tanggal</th>
    <th>Kasir</th>
    <th>Total</th>
</tr>
@foreach($transactions as $t)
<tr>
    <td>{{ $t->created_at->format('d-m-Y') }}</td>
    <td>{{ $t->user->name ?? '-' }}</td>
    <td>Rp {{ number_format($t->total) }}</td>
</tr>
@endforeach
</table>
@endsection

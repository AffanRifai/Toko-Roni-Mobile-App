# 📊 CHART PANJANG - PERUBAHAN SUMMARY

## ✅ Perubahan Sudah Dilakukan

Saya telah **memanjangkan chart** dengan mengubah multiplier dari **80** menjadi **120**.

---

## 📍 SYNTAX YANG MENGONTROL PANJANG CHART

**File:** `lib/home/landing_page.dart`  
**Baris:** ~1277-1288

### Kode Saat Ini

```dart
SingleChildScrollView(
  scrollDirection: Axis.horizontal,
  child: SizedBox(
    width: (dateLabels.length * 120).toDouble(),     // ← PANJANG CHART
    height: 300,                                      // ← TINGGI CHART
    child: CustomPaint(
      size: Size((dateLabels.length * 120).toDouble(), 300),  // ← HARUS SAMA DENGAN width
      painter: _DetailedLineChartPainter(
        penjualan: penjualan,
        stokKeluar: stokKeluar,
        days: dateLabels,
      ),
    ),
  ),
),
```

---

## 🎯 Cara Kerja Multiplier

```
Chart Width = dateLabels.length × MULTIPLIER

Contoh untuk 30 Hari (5 minggu):
  Sebelum: 5 × 80 = 400px ✗ (sempit)
  Sekarang: 5 × 120 = 600px ✓ (lebih panjang)

Contoh untuk 90 Hari (13 minggu):
  Sebelum: 13 × 80 = 1040px
  Sekarang: 13 × 120 = 1560px ✓ (jauh lebih panjang)
```

---

## 📋 MULTIPLIER REFERENCE

Jika ingin mengubah panjang chart, cukup ubah angka **120** ini:

| Angka | 7 Hari | 30 Hari | 90 Hari | Deskripsi |
|-------|--------|---------|---------|-----------|
| **60** | 420px | 300px | 780px | Terlalu sempit |
| **80** | 560px | 400px | 1040px | Sempit |
| **100** | 700px | 500px | 1300px | Sedang |
| **120** | 840px | 600px | 1560px | **Panjang (sekarang)** ✓ |
| **150** | 1050px | 750px | 1950px | Sangat panjang |
| **180** | 1260px | 900px | 2340px | Ekstrem panjang |

---

## 🔧 Cara Edit Sendiri

### Langkah 1: Buka File

```
lib/home/landing_page.dart
```

### Langkah 2: Cari Baris ~1277

Gunakan Ctrl+G untuk goto line 1277, atau cari:

```
width: (dateLabels.length * 120).toDouble(),
```

### Langkah 3: Ubah Angka 120

Misalnya ingin lebih panjang lagi:

```dart
// Sebelum:
width: (dateLabels.length * 120).toDouble(),

// Ubah menjadi (contoh):
width: (dateLabels.length * 150).toDouble(),
```

### Langkah 4: Update CustomPaint Size

Cari baris di bawahnya yang sama, dan ubah juga:

```dart
// Sebelum:
size: Size((dateLabels.length * 120).toDouble(), 300),

// Ubah menjadi:
size: Size((dateLabels.length * 150).toDouble(), 300),
```

### ⚠️ PENTING

Kedua angka **HARUS SAMA**. Jika tidak sama, chart akan terpotong!

---

## 📊 Hasil Sekarang

| Filter | Panjang Width | Status |
|--------|--------------|--------|
| 7 Hari | 7 × 120 = **840px** | Cukup lebar ✓ |
| 30 Hari | 5 × 120 = **600px** | Lebih panjang ✓ |
| 90 Hari | 13 × 120 = **1560px** | Sangat panjang, perlu scroll ✓ |

---

## 🎨 Cara Mengatur Spacing Per Filter (ADVANCED)

Jika ingin spacing **berbeda** untuk setiap filter:

```dart
@override
Widget build(BuildContext context) {
  final days = int.parse(_selectedFilter.split(' ')[0]);
  final dateLabels = _generateDateLabels(days);
  
  // Tentukan spacing per filter
  final int chartSpacing;
  if (_selectedFilter == '7 Hari') {
    chartSpacing = 100;   // Lebih compact
  } else if (_selectedFilter == '30 Hari') {
    chartSpacing = 120;   // Medium
  } else {
    chartSpacing = 100;   // 90 hari lebih compact (karena sudah 13 minggu)
  }
  
  // Gunakan chartSpacing di bawah:
  return Column(
    children: [
      // ... code lainnya ...
      SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: SizedBox(
          width: (dateLabels.length * chartSpacing).toDouble(),
          height: 300,
          child: CustomPaint(
            size: Size((dateLabels.length * chartSpacing).toDouble(), 300),
            painter: _DetailedLineChartPainter(
              penjualan: penjualan,
              stokKeluar: stokKeluar,
              days: dateLabels,
            ),
          ),
        ),
      ),
    ],
  );
}
```

---

## 💡 Tips & Tricks

### 1. Jika Performance Lambat (90 Hari)

Kurangi multiplier:

```dart
width: (dateLabels.length * 100).toDouble(),  // Dari 120 jadi 100
```

### 2. Jika Ingin Adjustable via UI

Tambah slider atau input field untuk mengubah multiplier real-time:

```dart
Slider(
  value: chartSpacing.toDouble(),
  min: 60,
  max: 200,
  onChanged: (value) {
    setState(() => chartSpacing = value.toInt());
  },
)
```

### 3. Responsive Sizing (Mobile/Desktop)

```dart
final chartSpacing = MediaQuery.of(context).size.width > 800 ? 120 : 100;
```

---

## ✨ Kesimpulan

**Syntax yang mengontrol panjang chart:**

```dart
width: (dateLabels.length * 120).toDouble()
```

- Ubah angka **120** untuk mengubah panjang
- Semakin besar angka = semakin panjang chart
- Jangan lupa update `CustomPaint size` juga!
- Kedua angka harus selalu **sama**

**Status:** ✅ Chart sudah dipanjangkan dengan baik!


![Logo](https://i.ibb.co.com/D48gBTN/sayawrt-unik-id.png)


# SayaWRT - Unik ID

#### Mudah membuat unik id pada framework Laravel

## Pemasangan

```bash
  composer require sayawrt/unik-id
```
## Gimana Sih Cara Pakenya?
gunakan ini setiap akan digunakan

```bash
  use SayaWRT/Unik/IdGenerator;
```

```bash
  public function store(Request $request){

	$id = IdGenerator::generate(['table' => 'todos', 'length' => 6, 'prefix' => date('y')]);

	$todo = new Todo();
	$todo->id = $id;
	$todo->title = $request->get('title');
	$todo->save();

}
```
### Parameter
Kamu harus memberikan array asosiatif kedalam generate, yang berisi table, length, prefix

`table` table yang kamu gunakan

`field` kolom yang digunakan pada table / default 'id'

`length` panjang total karakter unik id

`prefix` kode prefix (bebas) yang mau kamu gunakan

`reset_on_prefix_change` default true, jika prefix berubah akan kembali ke angka 1


```bash
$config = [
    'table' => 'todos',
    'length' => 6,
    'prefix' => date('y')
];

// now use it
$id = IdGenerator::generate($config);
```

```bash
// use within single line code
$id = IdGenerator::generate(['table' => 'todos', 'length' => 6, 'prefix' => date('y')]);

// output: 160001
```

#### Contoh 2
```bash
$id = IdGenerator::generate(['table' => 'invoices', 'length' => 10, 'prefix' =>'INV-']);
//output: INV-000001
```

#### Contoh 3
```bash
$id = IdGenerator::generate(['table' => 'invoices', 'length' => 10, 'prefix' =>date('ym')]);
//output: 1910000001
```

#### Contoh 4
```bash
$id = IdGenerator::generate(['table' => 'products','field'=>'pid', 'length' => 6, 'prefix' =>date('P')]);
//output: P00001
```

#### Contoh 5

#### pada contoh dibawah ini menggunakan "reset_on_prefix_change" bernilai TRUE

### reset ID Tahunan

```bash
$id = IdGenerator::generate(['table' => 'invoices', 'length' => 10, 'prefix' =>date('y')]);
//output: 2000000001,2000000002,2000000003
//output: 2100000001,2100000002,2100000003
```

### reset ID Bulanan

```bash
$id = IdGenerator::generate(['table' => 'invoices', 'length' => 10, 'prefix' =>date('ym')]);
//output: 1912000001,1912000002,1912000003
//output: 2001000001,2001000002,2001000003
```

### atau perubahan apapun pada prefix

```bash
$id = IdGenerator::generate(['table' => 'products', 'length' => 6, 'prefix' => $prefix]);
//output: A00001,A00002,B00001,B00002
```
## Dukungan

Terimakasih atas dukungannya.

<?php
session_start();
$Server = "localhost";
$User = "root";
$Pwd = "";
$BD = "Almacen";
$Conexion = mysqli_connect($Server, $User, $Pwd, $BD);
// $Consulta = "Select * from Articulos";
// Comprueba si No existe el post del filtro
if(!isset($_POST['Filtro']) || $_POST['Filtro']=="Todo"){
    $Consulta = "Select * from Articulos";

}else{
    $Consulta = "Select * from Articulos where Categoria = '".$_POST['Filtro']."'";
}
$result = $Conexion->query($Consulta);
if ($result->num_rows > 0) {
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['Id_Art'],
            'name' => $row['Nombre'],
            'alto' => $row['Alto'],
            'largo' => $row['Largo'],
            'fondo' => $row['Fondo'],
            'material' => $row['Material'],
            'category' => $row['Categoria'],
            'image' => $row['Imagen'],
            'price' => $row['Precio'],
            'exist' => $row['Existencias'],
        ];
        #echo $products['id'];
    }
}
//Función para calcular el total en el precio de los articulos
function calculateCartTotal()
{
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['quantity'] * $item['product']['price'];
        }
    }
    return $total;
}
//Funcion para aumentar el contador del carrito
// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
        $product_name = $_POST['product_name'];
        $quantity = $_POST['quantity'];

        // Buscar el producto por nombre
        $product = array_filter($products, function ($p) use ($product_name) {
            return $p['name'] === $product_name;
        });

        // Obtener el primer elemento del array (el producto)
        $product = reset($product);

        if ($product) {
            $product_id = $product['id'];

            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = ['quantity' => $quantity, 'product' => $product];
            }

            if (isset($_SESSION['cart_count'])) {
                $_SESSION['cart_count'] += $quantity;
            } else {
                $_SESSION['cart_count'] = $quantity;
            }
        }
    } elseif (isset($_POST['clear_cart'])) {
        unset($_SESSION['cart']);
        unset($_SESSION['cart_count']);
    }
}
if(isset($_SESSION['User'])){
    echo '
    <style>
    #S{
        display:block;
    }
    .Log{
        display:none;
    } 
    #ContImage{
        display:none;
    }
    </style>
    ';
}else{
    echo '
    <style>
    #S{
        display:none;
    }
    .Cant{
        display:none;

    }
    #Add{
        display:none;
    }
    #Br{
        display:none;
    }
    .Log{
        display:block;
    }
    .LblExist{
        display: none;   
    }
    #HrDetailInventary{
        display:none;
    }
    #DetailInventary{
        display:none;
    }
    </style>
    ';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista general de productos</title>
    <!-- Enlace para jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <!--Enlaces para la tipografía-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@200&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@200&display=swap" rel="stylesheet">    
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Afacad&display=swap" rel="stylesheet">
    <!--Cambiar el icono de la página en la pestaña superior de búsqueda--> 
    <link rel="shortcut icon" href="../Img/LogoCarpFavicon.png" type="image/x-icon">
    <!--Enace para la hoja de estilos de Css-->
    <link rel="stylesheet" href="StyleVistaProductos4.css">
    <!--Enlace para iconos de fontawesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <!---Modal del carrito-->
         <div id="ContentModalCarrito">    
            <div id="ContenedorP">
                <div id="carritoModal">
                    <div class="ModalHead">
                         <span id="closeModal" onclick="closeModal()">&times;</span>
                    </div>
                <span id="TitleModalCart">Carrito de Compras</span>
                <ul>
                            <?php if (isset($_SESSION['cart'])) : ?>
                                <?php foreach ($_SESSION['cart'] as $item) : ?>
                                    <li class="BodyModalCart">
                                        <?php echo $item['product']['name']; ?> -
                                        Cantidad: <?php echo $item['quantity']; ?> -
                                        Subtotal: $<?php echo $item['quantity'] * $item['product']['price']; ?>
                                    </li>
                                <?php endforeach; ?>
                                <li class="BodyModalCart">Total del Carrito: $<?php echo calculateCartTotal(); ?></li>
                                <form aclass="BodyModalCart"ction="VistaProductos.php" method="post">
                                    <button type="submit" name="clear_cart" id="ClearCart"><i class="fa-regular fa-trash-can"></i> Vaciar carrito</button>
                                </form>
                                <button title="Continuar" id="Continue"><i class="fa-solid fa-check-double"></i><a href="Pagos/Pago.php">Continuar</a></button>
                            <?php else : ?>
                                <li class="BodyModalCart">El carrito está vacío</li>
                            <?php endif; ?>
                        </ul>
                        <hr>
                <button onclick="closeModal()"class="BtnClose" title="Cerrar"><i class="fa-solid fa-xmark"></i>Cerrar</button>
             </div>
            </div>
            
        </div>

    <!--Fin del modal del carrito-->
    <div id="Encabezado">
        <table id="ContenidoE">
            <tr>
                <td id="S">
                    <span id="TcC">Carrito de compras</span>
                    <?php 
                    if(isset($_SESSION['TotalC'])){
                        echo $_SESSION['TotalC'];
                    }
                        /*echo $_SESSION['Carrito']['Cantidad'];
                        echo $_SESSION['Carrito']['Titulo'];
                        echo $_SESSION['Carrito']['Precio']; */
                    ?>
                    <br>
                    <section id="ContentCartCount">
                        <div id="cart-count"><?php echo isset($_SESSION['cart_count']) ? $_SESSION['cart_count'] : 0; ?></div>
                    </section>
                    <div id="ContentMenu"><i class="fa-solid fa-cart-plus fa-flip-horizontal" id="IconoCarrito" onclick="openModal()" title="Abrir carrito de compras"></i></div>
                </td>
                <td class="ContEncabezado" id="ContImage">
                    <img src="../Img/LogoCarp.png" alt="LogoTipo" title="Logo_CVM-M">
                </td>
                <td class="ContEncabezado" id="ContTitle">
                    <span>Vista general de los productos</span>
                </td>
                <td class="ContEncabezado" id="ContBtn">
                    <button id="BtnRegresar"> 
                        <i class="fa-solid fa-arrow-left-long"></i>
                        <a href="../index.php">Regresar</a>
                    </button>
                </td>
            </tr>
        </table>
    </div>


    <div id="Subtitulo">
    <span>
        Bienvenido a la vista general de los productos; aquí podra encontrar todos los productos que disponemos!!
    </span>
    <hr>
    </div>
    <nav id="Busqueda">
        <label id="TitleLeft" for="InputSearch">Busqueda general</label>
        <input type="search" placeholder="Búsqueda general" id="InputSearch">
        <i class="fa-solid fa-magnifying-glass" for="InputSearch" id="SearchIcon"></i>
    </nav>
    </section>
    <!-- Modal dinámico para el producto y su imágen -->
    <?php foreach ($products as $product) : ?>
    <div class="ModalImg" id="Open<?php echo $product['name']; ?>">
        <div>
            <div class="modal-overlay" onclick="OutModalClose('<?php echo $product['name']; ?>')"></div>
        </div>
        <div class="ModalContImg">
            <div class="ModalBodyImg">
                <img src="../Img/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="ImgModal">
            </div>
        </div>
    </div>
<?php endforeach; ?>
<!-- Ex -->
    <section id="ResultSearch">
        <!-- Mostrar los resultados de busqueda -->
    </section>
        <!-- Formulario para los filtros -->
    <section id="CotentBtnF">
        <form action="VistaProductos.php" method="post" id="FormFilter">
            <select name="Filtro" id="SelectFilter">
            <option value="" selected disabled id="Filtro">Selecionar filtro</option>
            <?php 
            $ConsultaCategorías = "Select Distinct Categoria from Articulos";
            $SustraccionCategorías = $Conexion->query($ConsultaCategorías);
            $Categoria = [];

            while ($row = mysqli_fetch_assoc($SustraccionCategorías)) {
                $Categoria[] = $row['Categoria'];
            }
            ?>
            <?php foreach ($Categoria as $category) : ?>
                <option class="Category" value="<?php echo $category; ?>"><?php echo $category; ?></option>
            <?php endforeach; ?>
                <option value="Todo" class="Category">Todo</option>
            </select>
            <button type="submit" id="BtnAplicar"><i class="fa-solid fa-filter"></i> Aplicar</button>
        </form>
    </section>
    <table id="ContentAll">
        <tr>
            <?php $productCount = 0; ?>
            <?php foreach ($products as $product) : ?>
                <td class="Art">
                    <section class="Producto">
                        <article class="Contenido">
                            <label id="TitleModalProducto">Nombre: <?php echo $product['name'];?> </label>
                            <hr>
                            <br>
                            <img src="../Img/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="card-img-top"   onclick="Sh('<?php echo $product['name']; ?>')">
                            <br>
                            <!-- <div class="Decripcion">Cómoda elaborada con madera de pino de 3 cajones estilo rústico liso.</div> -->
                            <br>
                            <div id="PriceModal"><?php echo '$',$product['price'];?></div>
                            <form action="VistaProductos.php" method="post">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="product_name" value="<?php echo $product['name']; ?>">
                                <label class="Cant">Cantidad: </label>
                                <input type="number" name="quantity" value="1" min="1" max="10" class="Cant" id="InputCant">
                                <br><br>
                                <label class="LblExist" style="font-weight: 500;">Existencias: <?php echo $product['exist']; ?></label>
                                <br><br>
                                <button type="submit" name="add_to_cart" id="Add" class="Agregar"> <i class="fa-solid fa-cart-plus fa-flip-horizontal"></i>Agregar al carrito</button>
                                <button class="Log"><i class="fa-solid fa-user-check"></i> <a href="Inicio de sesion.php">Registrese o inicie sesión para comprar...</a></button>
                                <br id="Br">
                                <button type="button" onclick="openCustomModal('<?php echo $product['name']; ?>')" id="BtnVerMás">Detalles</button>

                            </form>

                        </article>
                    </section>
                </td>

                <?php
                    $productCount++;
                    if ($productCount == 2) {
                        echo '</tr><tr>';
                        $productCount = 0;
                    }
                ?>
            <?php endforeach; ?>
        </tr>
    </table>
    <?php foreach ($products as $product) : ?>
    <!-- Modal dinámico para cada producto -->
    <div class="custom-modal" id="myModal<?php echo $product['name']; ?>">
        <div>
            <div class="modal-overlay" onclick="closeCustomModal('<?php echo $product['name']; ?>')"></div>
        </div>
        <div class="modal-content">
            <div class="ModalHead">
                <span id="CloseModalDn" onclick="closeCustomModal('<?php echo $product['name']; ?>')">&times;</span>
            </div>
            <span class="modal-title">Detalles del producto - <?php echo $product['name']; ?></span>
            <hr>
            <div class="modal-body">
                <label class="TituloArt" style="font-weight: 400;">Precio: $<?php echo $product['price']; ?></label>
                <hr style="font-weight: 900; color: black; width: 85%;">
                <label class="CategoriaArt" style="font-weight: 500;">Categoria: <?php echo $product['category']; ?></label>
                <hr id="HrDetailInventary" style="font-weight: 900; color: black; width: 90%">
                <label class="CategoriaArt" id="DetailInventary" style="font-weight: 500;">Inventario: <?php echo $product['exist']; ?></label>
                <hr style="font-weight: 900; color: black; width: 90%">
                <label class="CategoriaArt" style="font-weight: 500;">Material: <?php echo $product['material']; ?></label>
                <hr style="font-weight: 900; color: black; width: 90%">
                <label class="Medidas" style="font-weight: 600;">Medidas: <span id="Alto"> Alto - <?php echo $product['alto']; ?> </span> <span id="Largo"> | Largo - <?php echo $product['largo']; ?></span></label>
                <?php if ($product['category'] !== 'Marcos') : ?> <!--Condición qe verifica si el producto no tiene como categoría 'Marcos' -->
                    <!-- Mostrar fondo solo si la categoría no es 'Marcos' -->
                    <span id="Fondo" class="Medidas" style="font-weight: 600;">| Fondo - <?php echo $product['fondo']; ?> </span> 
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="BtnClose" onclick="closeCustomModal('<?php echo $product['name']; ?>')"><i class="fa-solid fa-xmark"></i>Cerrar</button>
            </div>
        </div>
    </div>
    <!-- Fin del modal dinámico para detalles del producto -->
<?php endforeach; ?>
    <footer class="footer">
        <p>Domicilio: Zapopan | N.1231 | Col: El Rosal</p>
    </footer>
    <script>
            function Sh(productName) {
        var modalId = "Open" + productName;
        var modal = document.getElementById(modalId); 

        if (modal) { 
            modal.style.display = "block"; 
        }
    }
    function OutModalClose(productName){
        document.getElementById('Open' + productName).style.display = 'none';
    }
    </script>
    <script src="ScriptVistaProductos.js">

/*     function openCustomModal(name) {
        document.getElementById('myModal' + name).style.display = 'block';
    }

    function closeCustomModal(name) {
        document.getElementById('myModal' + name).style.display = 'none';
    }

        function openModal(carritoModal) {
            document.getElementById("carritoModal").style.display = "block";
            document.getElementById("ContentModalCarrito").style.display="block";
        }

        function closeModal(carritoModal) {
            document.getElementById("carritoModal").style.display = "none";
            document.getElementById("ContentModalCarrito").style.display="none";
        } */
    </script>
</body>
</html>
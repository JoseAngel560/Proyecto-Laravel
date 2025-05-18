<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Repuestos de Motos</title>
        <link rel="stylesheet" href="{{ asset('css/creadores.css') }}">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="container">
        <h1>MOTO REPUESTO DIVINO NIÑO</h1>

        <h2>Quiénes Somos</h2>
        <p>
            Moto Repuesto Divino Niño es una empresa dedicada a ofrecer repuestos de calidad para motocicletas, 
            garantizando disponibilidad, confianza y un servicio excepcional a nuestros clientes.
        </p>

        <h2>Equipo</h2>
        <p>
            Nuestro equipo está conformado por profesionales comprometidos con la innovación y la mejora continua, 
            quienes trabajan día a día para brindar soluciones eficientes y un excelente servicio.
        </p>

        <div class="team">
            <div class="team-member">
                <img src="{{ asset('Images/perfil-de-usuario.png') }}" alt="Jose Angen Rugama Montenegro">
                <h3>Jose Angen Rugama Montenegro</h3>
                <p>+505 5812 6336</p>
                <p>joseangelrugtd@gmail.com</p>
            </div>
            <div class="team-member">
                <img src="{{ asset('Images/perfil-de-usuario.png') }}" alt="Rolando Jose Rodriguez Granado">
                <h3>Rolando Jose Rodriguez Granado</h3>
                <p>+505 8833 5614</p>
                <p>rolandojose255@gmail.com</p>
            </div>
            <div class="team-member">
                <img src="{{ asset('Images/perfil-de-usuario.png') }}" alt="Jefferson Joseph Montenegro Cantarero">
                <h3>Jefferson Joseph Montenegro Cantarero</h3>
                <p>+505 8503 9538</p>
                <p>jjmonca08@gmail.com</p>
            </div>
        </div>

        <footer>
            <p>&copy; 2024 Moto Repuesto Divino Niño. Todos los derechos reservados.</p>
        </footer>
    </div>
</body>
</html>
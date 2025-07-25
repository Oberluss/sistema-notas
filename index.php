<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Notas</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- React y ReactDOM -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    
    <!-- Babel -->
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>

    <!-- jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Archivo de estilos -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-gray-50">

    <?php
    session_start();
    $isLoggedIn = isset($_SESSION['user_id']);
    $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    $userName = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : '';
    $userRole = $isAdmin ? 'Administrador' : 'Usuario';
    ?>


    
    <div id="root"></div>
    
    <script type="text/babel">
        const { useState } = React;

        const App = () => {
            const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false' ?>;
            const isAdmin = <?php echo $isAdmin ? 'true' : 'false' ?>;
            const userName = <?php echo json_encode($userName) ?>;
            const userRole = <?php echo json_encode($userRole) ?>;

            const initialLines = Array(10).fill().map(() => ({ 
                units: '', 
                description: '', 
                price: '', 
                total: 0 
            }));

            const [clientData, setClientData] = useState({
                name: '',
                address: '',
                phone: '',
                date: new Date().toISOString().split('T')[0],
                documentType: 'nota'
            });

            const [invoiceLines, setInvoiceLines] = useState(initialLines);

            const calculateTotal = () => {
                return invoiceLines.reduce((sum, line) => sum + (line.total || 0), 0);
            };

            const updateLine = (index, field, value) => {
                const newLines = [...invoiceLines];
                newLines[index][field] = value;
                
                if (field === 'units' || field === 'price') {
                    const units = parseFloat(newLines[index].units) || 0;
                    const price = parseFloat(newLines[index].price) || 0;
                    newLines[index].total = units * price;
                }
                
                setInvoiceLines(newLines);
            };

            const guardarNota = async () => {
                if (!isLoggedIn) {
                    alert('Debe iniciar sesión para guardar notas');
                    window.location.href = 'admin/login.php';
                    return;
                }

                try {
                    const response = await fetch('guardar_nota.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            nombre: clientData.name,
                            direccion: clientData.address,
                            telefono: clientData.phone,
                            tipo: clientData.documentType,
                            fecha: clientData.date,
                            total: calculateTotal(),
                            items: invoiceLines.filter(line => line.units || line.description || line.price)
                        })
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        alert('Nota guardada correctamente');
                        window.location.href = 'vernotas.php';
                    } else {
                        throw new Error(result.error);
                    }
                    
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al guardar la nota');
                }
            };

            return (
                <div className="min-h-screen bg-gray-50">
                    <header className="sticky top-0 bg-white shadow-md z-10">
                        <div className="container mx-auto px-4 py-4">
                            <div className="flex justify-between items-center">
                                <div className="flex items-center space-x-4">
                                    <h1 className="text-2xl md:text-3xl font-bold text-gray-800">
                                        Sistema de Notas
                                    </h1>
                                    {isLoggedIn && (
                                        <div className="text-gray-600">
                                            <span className="font-medium">
                                                Bienvenido, {userName}
                                            </span>
                                            <span className="mx-1">|</span>
                                            <span className="text-gray-500">
                                                {userRole}
                                            </span>
                                        </div>
                                    )}
                                </div>
                                {isLoggedIn ? (
                                    <button
                                        onClick={() => window.location.href = 'admin/logout.php'}
                                        className="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors"
                                    >
                                        Cerrar Sesión
                                    </button>
                                ) : (
                                    <button
                                        onClick={() => window.location.href = 'admin/login.php'}
                                        className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors"
                                    >
                                        Iniciar Sesión
                                    </button>
                                )}
                            </div>
                        </div>
                    </header>
                    
                    <main className="container mx-auto px-4 py-6">
                        <div className="space-y-6">
                            {/* Fecha y Tipo de Documento */}
                            <div className="bg-white p-4 md:p-6 rounded-lg shadow-md">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-600 mb-1">Fecha</label>
                                        <input
                                            type="date"
                                            value={clientData.date}
                                            onChange={(e) => setClientData({...clientData, date: e.target.value})}
                                            className="w-full p-2 border rounded"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-600 mb-1">Tipo de Documento</label>
                                        <select 
                                            value={clientData.documentType}
                                            onChange={(e) => setClientData({...clientData, documentType: e.target.value})}
                                            className="w-full p-2 border rounded"
                                        >
                                            <option value="nota">Nota</option>
                                            <option value="presupuesto">Presupuesto</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {/* Datos del Cliente y Empresa */}
                            <div className="bg-white p-4 md:p-6 rounded-lg shadow-md">
                                <h2 className="text-lg md:text-xl font-semibold mb-4">Datos del Cliente</h2>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {/* Cliente */}
                                    <div className="space-y-3">
                                        <input
                                            type="text"
                                            placeholder="Nombre del cliente"
                                            value={clientData.name}
                                            onChange={(e) => setClientData({...clientData, name: e.target.value})}
                                            className="w-full p-3 border rounded shadow-sm"
                                        />
                                        <input
                                            type="text"
                                            placeholder="Dirección"
                                            value={clientData.address}
                                            onChange={(e) => setClientData({...clientData, address: e.target.value})}
                                            className="w-full p-3 border rounded shadow-sm"
                                        />
                                        <input
                                            type="tel"
                                            placeholder="Teléfono"
                                            value={clientData.phone}
                                            onChange={(e) => setClientData({...clientData, phone: e.target.value})}
                                            className="w-full p-3 border rounded shadow-sm"
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Tabla de Detalles */}
                            <div className="bg-white p-4 md:p-6 rounded-lg shadow-md">
                                <h2 className="text-lg md:text-xl font-semibold mb-4">Detalles de la Nota</h2>
                                <div className="overflow-x-auto">
                                    <table className="w-full table-auto">
                                        <thead className="bg-gray-100">
                                            <tr>
                                                <th className="p-3 w-[15%] text-left">Unidades</th>
                                                <th className="p-3 w-[55%] text-left">Descripción</th>
                                                <th className="p-3 w-[15%] text-left">Precio</th>
                                                <th className="p-3 w-[15%] text-left">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {invoiceLines.map((line, index) => (
                                                <tr key={index} className="border-b">
                                                    <td className="p-3">
                                                        <input
                                                            type="number"
                                                            value={line.units}
                                                            onChange={(e) => updateLine(index, 'units', e.target.value)}
                                                            className="w-full p-2 border rounded"
                                                        />
                                                    </td>
                                                    <td className="p-3">
                                                        <input
                                                            type="text"
                                                            value={line.description}
                                                            onChange={(e) => updateLine(index, 'description', e.target.value)}
                                                            className="w-full p-2 border rounded"
                                                        />
                                                    </td>
                                                    <td className="p-3">
                                                        <input
                                                            type="number"
                                                            value={line.price}
                                                            onChange={(e) => updateLine(index, 'price', e.target.value)}
                                                            className="w-full p-2 border rounded"
                                                        />
                                                    </td>
                                                    <td className="p-3">
                                                        <input
                                                            type="number"
                                                            value={(line.total || 0).toFixed(2)}
                                                            readOnly
                                                            className="w-full p-2 border rounded bg-gray-50"
                                                        />
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                                <div className="mt-4 bg-gray-50 p-4 rounded-lg text-right">
                                    <span className="font-bold">Suma Total: </span>
                                    <span className="font-bold text-lg">{calculateTotal().toFixed(2)} €</span>
                                </div>
                            </div>
                        </div>

                        {/* Botones */}
                        <div className="mt-6 flex flex-col md:flex-row justify-end gap-2">
                            {isLoggedIn && (
                                <button
                                    className="w-full md:w-auto bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition-colors"
                                    onClick={guardarNota}
                                >
                                    Guardar
                                </button>
                            )}
                            {isAdmin && (
                                <button
                                    className="w-full md:w-auto bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors"
                                    onClick={() => window.location.href = 'vernotas.php'}
                                >
                                    Ver Notas
                                </button>
                            )}
                        </div>
                    </main>
                </div>
            );
        };

        ReactDOM.render(<App />, document.getElementById('root'));
    </script>
</body>
</html>

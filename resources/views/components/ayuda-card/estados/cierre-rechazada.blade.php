
<div class="cierre-rechazada-container" style="
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 2rem;
    margin: 1.5rem 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    position: relative;
    border-left: 6px solid #ef4444;
">
    
    <!-- Header con icono y título -->
    <div style="
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 3px solid #ef4444;
    ">
        <div style="
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        ">
            <span style="font-size: 2rem;">❌</span>
        </div>
        <div>
            <h3 style="
                color: #2d3748;
                font-size: 1.75rem;
                font-weight: 700;
                margin: 0;
            ">Solicitud Rechazada</h3>
            <p style="
                color: #ef4444;
                font-size: 1.1rem;
                margin: 0.25rem 0 0 0;
                font-weight: 600;
            ">La solicitud ha sido <strong>RECHAZADA</strong></p>
        </div>
    </div>

    <!-- Mensaje principal -->
    <div style="
        background: #fef2f2;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid #ef4444;
    ">
        <p style="
            color: #2d3748;
            font-size: 1.1rem;
            line-height: 1.6;
            margin: 0 0 1rem 0;
            text-align: center;
        ">
            😔 <strong>Lamentamos que tu solicitud haya sido rechazada.</strong>
        </p>
        <p style="
            color: #4a5568;
            font-size: 1rem;
            line-height: 1.6;
            margin: 0;
            text-align: center;
        ">
            Si crees que se trata de un error o quieres saber si puedes presentar alegaciones, contacta con nosotros y te orientaremos.
        </p>
    </div>

</div>

<!-- Estilos adicionales para mejorar la responsividad -->
<style>
    @media (max-width: 768px) {
        .cierre-rechazada-container {
            padding: 1.5rem;
            margin: 1rem 0;
        }
        
        .cierre-rechazada-container h3 {
            font-size: 1.5rem !important;
        }
        
        .cierre-rechazada-container .botones-accion {
            flex-direction: column;
            align-items: stretch;
        }
        
        .cierre-rechazada-container .botones-accion a {
            justify-content: center;
        }
    }
</style>

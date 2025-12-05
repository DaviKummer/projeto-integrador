document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('nav ul li a');
    const conteudoPrincipal = document.getElementById('conteudo-principal');

    // Funções de Ação
    // -------------------------------------------------------------
    
    /**
     * Configura o ouvinte de evento para o botão "Calcular IMC".
     * Esta função DEVE ser chamada APÓS o HTML da calculadora ser injetado.
     */
    function configurarCalculadoraIMC() {
        const botaoCalcular = document.getElementById('calcular-imc');
        
        // ❌ CHAMADA INICIAL REMOVIDA:
        // carregarHistoricoIMC(); 
        // Agora, carregarHistoricoIMC é chamada no manipulador de clique do link 'saude'
        // e após o cálculo/salvamento do IMC, garantindo que o elemento existe.

        // Verifica se o botão existe antes de adicionar o listener
        if (botaoCalcular) {
            botaoCalcular.addEventListener('click', function() {
                // 1. Coleta os valores dos campos
                const pesoInput = document.getElementById('peso');
                const alturaInput = document.getElementById('altura');
                const resultadoDiv = document.getElementById('resultado-imc');

                // Garante que os valores são lidos como números
                const peso = parseFloat(pesoInput.value);
                const altura = parseFloat(alturaInput.value);

                // 2. Validação básica dos dados
                if (isNaN(peso) || isNaN(altura) || peso <= 0 || altura <= 0) {
                    resultadoDiv.innerHTML = '<p style="color: red;"> Insira valores válidos para peso e altura.</p>';
                    return; // Interrompe a função se os dados forem inválidos
                }

                // 3. Cálculo do IMC: IMC = Peso / (Altura * Altura)
                const imc = peso / (altura * altura);
                const imcFormatado = imc.toFixed(2); // Formata para 2 casas decimais

                // 4. Classificação do IMC
                let classificacao;
                let cor;
                
                if (imc < 18.5) {
                    classificacao = 'Abaixo do peso';
                    cor = 'orange';
                } else if (imc >= 18.5 && imc < 25) {
                    classificacao = 'Peso normal (Saudável)';
                    cor = 'green';
                } else if (imc >= 25 && imc < 30) {
                    classificacao = 'Sobrepeso';
                    cor = 'darkorange';
                } else if (imc >= 30 && imc < 35) {
                    classificacao = 'Obesidade Grau I';
                    cor = 'red';
                } else if (imc >= 35 && imc < 40) {
                    classificacao = 'Obesidade Grau II (Severa)';
                    cor = 'darkred';
                } else {
                    classificacao = 'Obesidade Grau III (Mórbida)';
                    cor = 'purple';
                }

                // 5. Exibe o resultado
                resultadoDiv.innerHTML = `
                    <h4>Seu IMC é: ${imcFormatado}</h4>
                    <p>Classificação: <strong style="color: ${cor};">${classificacao}</strong></p>
                `;
                
                // ---- ENVIAR AO PHP PARA SALVAR NO BANCO ----
                fetch("salvar_imc.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/json"},
                    body: JSON.stringify({
                        peso: peso,
                        altura: altura,
                        imc: imc,
                        classificacao: classificacao
                    })
                })
                .then(res => res.json())
                .then(json => {
                    console.log("Resposta do servidor:", json);
                    // ✅ A função é chamada aqui para ATUALIZAR a tabela logo após salvar
                    carregarHistoricoIMC(); 
                })
                .catch(error => {
                    console.error("Erro ao salvar o IMC:", error);
                    // Opcional: Mostrar erro para o usuário
                    resultadoDiv.innerHTML += '<p style="color: red;"> Erro ao salvar histórico.</p>';
                });
            });
        }
    }
    
    // Objeto de Mapeamento: Contém o HTML para cada link
    const conteudoDinamico = {
        'saude': `
            <div class="saude-container"> <h3> Calculadora de Índice de Massa Corporal (IMC)</h3>
                <p>O IMC é uma medida internacional usada para calcular se uma pessoa está com o peso ideal.</p>
                
                <div class="imc-form">
                    <div class="input-group">
                        <label for="peso">Peso (kg):</label>
                        <input type="number" id="peso" placeholder="Ex: 70" step="0.01">
                    </div>
                    
                    <div class="input-group">
                        <label for="altura">Altura (m):</label>
                        <input type="number" id="altura" placeholder="Ex: 1.75" step="0.01">
                    </div>
                    
                    <button id="calcular-imc">Calcular IMC</button>
                    
                    <div id="resultado-imc">
                    
                    </div>
                </div>
                <p class="nota-imc"> 
                    <a href="https://www.gov.br/conitec/pt-br/midias/protocolos/resumidos/PCDTResumidodeSobrepesoObesidade.pdf.pdf" target="_blank" 
                    style="display: inline-flex; align-items: center; text-decoration: none; color: inherit;"> 
                
                        Confira recomendações do Ministário da Saúde 
                
                        <img src="link-externo.png"
                        style="height: 1em; margin-left: 5px;">
                        </a>
                </p>
            </div>
            
            <div id="historico-imc" style="margin-top:25px;"></div>
            <div class="pcdt-conteudo">
    
                <h1>Sobrepeso e Obesidade em Adultos</h1>
                
                <h2>Estratégias de Prevenção</h2>
                <p>
                    Alimentação Adequada e Saudável: Deve ir além do aspecto nutricional, considerando a adequação cultural, social e econômica (em diálogo com a Política Nacional de Segurança Alimentar e Nutricional - PNSAN).
                    <br><br>
                    Atividade Física: A prática regular, associada a hábitos saudáveis de alimentação, auxilia no controle de peso e diminui o risco de doenças crônicas não transmissíveis
                </p>
                
                <h2>Intervenção e Tratamento</h2>
                <p>
                    Redução dos Ambientes Obesogênicos: Envolve a adoção de políticas intersetoriais para reverter fatores ambientais que promovem a obesidade, como:
                    <br>
                    Regulação do marketing de alimentos ultraprocessados.
                    <br>
                    Melhoria da rotulagem dos produtos.
                    <br>
                    Medidas fiscais para alimentos não saudáveis.
                    <br>
                    Mudanças na infraestrutura para aumentar espaços de recreação.
                </p>
                
                <h2>Casos Especiais</h2>
                <p>
                    Gestantes e lactantes: Estado Nutricional na Gestação
                    <br><br>
                    Bulimia e compulsão alimentar: Transtornos Alimentares
                </p>
                
            </div>
        `,
        'incendio': `
            <h3> Dicas de Prevenção de Incêndio</h3>
            
        `,
        // ... (resto do seu objeto conteudoDinamico)
    };

    // 4. Adiciona um "ouvinte de evento" (listener) a cada link
    links.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault(); 

            const pageKey = this.getAttribute('data-page');

            if (conteudoDinamico[pageKey]) {
                // 1. Injeta o HTML correspondente na área de conteúdo
                conteudoPrincipal.innerHTML = conteudoDinamico[pageKey];
                
                // 2. CHAMA A FUNÇÃO DE CONFIGURAÇÃO SE FOR A PÁGINA 'saude'
                if (pageKey === 'saude') {
                    configurarCalculadoraIMC();
                    // ✅ CHAMADA CORRIGIDA: Agora o histórico é carregado após
                    // o elemento 'historico-imc' ter sido injetado no DOM.
                    carregarHistoricoIMC(); 
                }
                
            } else {
                conteudoPrincipal.innerHTML = '<h3>Conteúdo não encontrado.</h3>';
            }
        });
    }); 
});

/**
 * Busca o histórico de IMC do backend e renderiza a tabela no frontend.
 * Também formata os valores para melhor visualização.
 */
function carregarHistoricoIMC() {
    fetch("busca_historico.php")
    .then(res => {
        if (!res.ok) {
            throw new Error(`Erro de rede: ${res.status}`);
        }
        return res.json();
    })
    .then(dados => {
        const area = document.getElementById("historico-imc");
        if (!area) return; // Retorna se o elemento não estiver no DOM

        if (dados.length === 0) {
            area.innerHTML = "<p>Nenhum registro de IMC encontrado.</p>";
            return;
        }

        let tabela = `
            <h3>Histórico de IMC</h3>
            <table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">
                <tr>
                    <th>Data</th>
                    <th>Peso (kg)</th>
                    <th>Altura (m)</th>
                    <th>IMC</th>
                    <th>Classificação</th>
                </tr>
        `;

        dados.forEach(l => {
            // ✅ CORREÇÃO: Formata os valores numéricos para 2 casas decimais
            const pesoFormatado = parseFloat(l.peso_kg).toFixed(2);
            const alturaFormatada = parseFloat(l.altura_m).toFixed(2);
            const imcFormatado = parseFloat(l.imc).toFixed(2);
            
            tabela += `
                <tr>
                    <td>${l.data_calculo}</td>
                    <td>${pesoFormatado}</td>
                    <td>${alturaFormatada}</td>
                    <td>${imcFormatado}</td>
                    <td>${l.classificacao}</td>
                </tr>
            `;
        });

        tabela += "</table>";
        area.innerHTML = tabela;
    })
    .catch(error => {
        console.error("Erro ao carregar o histórico:", error);
        const area = document.getElementById("historico-imc");
        if (area) {
             area.innerHTML = `<p style="color: red;">Ocorreu um erro ao carregar o histórico de IMC.</p>`;
        }
    });
}
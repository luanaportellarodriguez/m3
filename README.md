### **E-commerce de produtos criados por IA**

Um e-commerce onde o usuário envia uma imagem, escolhe um estilo artístico e a IA redesenha essa imagem nesse estilo. A IA gera a imagem, o usuário adiciona no carrinho e depois vai pros trâmites de checkout e finalização de compra.

### **Passo a passo do funcionamento**

##### 1. **Upload da Imagem**

Crie um formulário na página do produto:

-   Upload da foto.
-   Seleção de estilo: 3D, Fotorrealista, Aquarela ou Anime.

##### 2. **Envio para API de IA**

Quando o usuário clica no botão enviar:

-   A imagem e o prompt são enviados via **AJAX** para um endpoint customizado em Magento.
    (Inicialmente era feito com ajax, mas por questões de custos/token, fiz um mock)
-   Este endpoint usa a API da IA para gerar a imagem no estilo.

##### 3. **Renderização da imagem**

Com a imagem estilizada recebida:

-   A imagem é renderizada na tela.
-   O usuário tem como opção, adicionar ao carrinho, redesenhar ou descartar.

### **Parte técnica**

##### 1. **Tema**

-   Foi criado um tema com base no Luma, mas totalmente reformulado.

##### 2. **Módulos**

-   Foram criados módulos, templates, controllers específicos para atender a personalização das funcionalidades.

##### 3. **PDP**

-   A página de produto apresenta a imagem original e a imagem estilizada.
-   Por meio de um widget personalizado, o usuário tem a opção de retirar a arte impressa na loja.

---

### **Imagens do Projeto**

#### Página inicial

![Página inicial](../sandboxm3/app/design/frontend/AI/ai/web/images/previews/home.png)

#### Carregando imagem

![Carregando](../sandboxm3/app/design/frontend/AI/ai/web/images/previews/uploading.png)
![Carregada](../sandboxm3/app/design/frontend/AI/ai/web/images/previews/uploaded.png)

#### Estilizando imagem

![Estilizando](../sandboxm3/app/design/frontend/AI/ai/web/images/previews/styling.png)
![Estilizada](../sandboxm3/app/design/frontend/AI/ai/web/images/previews/stylized.png)

#### Página do Produto (PDP)

![PDP](../sandboxm3/app/design/frontend/AI/ai/web/images/previews/pdp.png)

#### Página do Carrinho

![Página do carrinho](../sandboxm3/app/design/frontend/AI/ai/web/images/previews/cart-page.png)

#### Minicart

![Minicart](../sandboxm3/app/design/frontend/AI/ai/web/images/previews/minicart.png)

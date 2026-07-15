<?php

return [
    'default' => [
        'Documento de identificação',
        'CPF',
        'Comprovante de residência',
        'Procuração assinada',
    ],

    'types' => [
        'Previdenciário' => [
            'Documento de identificação',
            'CPF',
            'Comprovante de residência',
            'CNIS atualizado',
            'Carteira de trabalho',
            'Documentos médicos, quando aplicável',
            'Procuração assinada',
        ],
        'Consumidor' => [
            'Documento de identificação',
            'CPF',
            'Comprovante de residência',
            'Contrato ou comprovante da relação de consumo',
            'Notas fiscais ou comprovantes de pagamento',
            'Conversas e protocolos de atendimento',
            'Procuração assinada',
        ],
        'Família' => [
            'Documento de identificação',
            'CPF',
            'Comprovante de residência',
            'Certidão de casamento ou nascimento',
            'Documentos dos filhos, quando aplicável',
            'Comprovantes de renda',
            'Procuração assinada',
        ],
    ],
];

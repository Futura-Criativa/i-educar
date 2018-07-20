<?php


namespace iEducar\Modules\Educacenso\Deficiencia;


class ValidaDeficienciaMultipla
{
    /**
     * @var array
     */
    private $combinacaoDeficiencias;

    public function __construct(CombinacaoDeficienciaMultipla $combinacao)
    {
        $this->combinacaoDeficiencias = $combinacao->getCombinacoes();
    }

    /**
     * @param array $deficiencias
     * @return bool
     */
    public function possuiDeficienciaMultipla($deficiencias)
    {
        if (empty($deficiencias)) {
            return null;
        }

        foreach ($this->combinacaoDeficiencias as $combinacao) {
            if (in_array($combinacao[0], $deficiencias) && in_array($combinacao[1], $deficiencias)) {
                return 1;
            }
        }

        return 0;
    }
}
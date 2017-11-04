<?php
namespace Application;

use ManaPHP\Mongodb;
use ManaPHP\Mvc\NotFoundException;

/**
 * @property \ManaPHP\Http\RequestInterface  request
 * @property \ManaPHP\Http\ResponseInterface $response
 * @property \ManaPHP\Mvc\HandlerInterface   $mvcHandler
 */
class Application extends \ManaPHP\Mvc\Application
{
    /**
     * @param \ManaPHP\Mvc\NotFoundException $e
     *
     * @return void
     * @throws \ManaPHP\Mvc\NotFoundException
     */
    protected function notFoundException($e)
    {
//            if ($this->request->isAjax()) {
//                return $this->response->setJsonContent([
//                    'code' => -1,
//                    'error' => $e->getMessage(),
//                    'data' => [
//                        'exception_trace' => explode('#', $e->getTraceAsString()),
//                        'exception_class' => get_class($e)
//                    ]
//                ]);
//            } else {
//                $this->response->redirect('http://www.manaphp.com/?exception_message=' . $e->getMessage())->sendHeaders();
//            }

        /** @noinspection PhpUnreachableStatementInspection */
        throw $e;
    }

    /**
     * @return void
     * @throws \ManaPHP\Alias\Exception
     * @throws \ManaPHP\Mvc\Application\Exception
     * @throws \ManaPHP\Mvc\NotFoundException
     * @throws \ManaPHP\Di\Exception
     * @throws \ManaPHP\Db\Exception
     * @throws \ManaPHP\Mvc\Application\NotFoundModuleException
     * @throws \ManaPHP\Mvc\View\Exception
     * @throws \ManaPHP\Renderer\Exception
     * @throws \ManaPHP\Mvc\Dispatcher\Exception
     * @throws \ManaPHP\Mvc\Router\Exception
     * @throws \ManaPHP\Event\Exception
     */
    public function main()
    {
        $this->configure->load('@app/config.php', 'dev');
		
        $this->registerServices();

        try {
            $this->mvcHandler->handle();
        } catch (NotFoundException $e) {
            $this->notFoundException($e);
        } catch (\ManaPHP\Security\Captcha\Exception $e) {
            if ($this->request->isAjax()) {
                $this->response->setJsonContent(['code' => __LINE__, 'error' => 'captcha is wrong.']);
            }
        }

        $this->response->send();
    }
}

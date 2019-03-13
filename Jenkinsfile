pipeline {
  agent any
  post {
    failure {
      updateGitlabCommitStatus name: "build", state: "failed"
    }
    success {
      updateGitlabCommitStatus name: "build", state: "success"
    }
  }
  options {
    gitLabConnection("gitlab.huixiaoer.com")
  }
  stages {
    stage('Start') {
      steps {
        updateGitlabCommitStatus name: 'build', state: 'pending'
      }
    }
    stage("DockerBuild") {
      when {
        branch "master"
      }
      steps {
        sh "docker build -t docker.huixiaoer.net/huixiaoer_laravel:${BRANCH_NAME}-${env.GIT_COMMIT} ."
        sh "docker tag docker.huixiaoer.net/huixiaoer_laravel:${BRANCH_NAME}-${env.GIT_COMMIT} docker.huixiaoer.net/huixiaoer_laravel:latest"
        sh "docker push docker.huixiaoer.net/huixiaoer_laravel:${BRANCH_NAME}-${env.GIT_COMMIT}"
        sh "docker push docker.huixiaoer.net/huixiaoer_laravel:latest"
      }
    }
  }
}